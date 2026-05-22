<?php

require_once dirname(__DIR__) . '/config.php';

class Mailer
{
    private $host;
    private $port;
    private $user;
    private $pass;
    private $encryption;
    private $from;
    private $sendgridApiKey;

    public function __construct()
    {
        $this->host = SMTP_HOST;
        $this->port = SMTP_PORT;
        $this->user = SMTP_USER;
        $this->pass = SMTP_PASS;
        $this->encryption = SMTP_ENCRYPTION;
        $this->from = EMAIL_FROM;
        $this->sendgridApiKey = SENDGRID_API_KEY;
    }

    public function send($to, $subject, $body, $replyTo = null)
    {
        if ($this->canUseSendGrid()) {
            $ok = $this->sendSendGrid($to, $subject, $body, $replyTo);
            if ($ok) {
                return true;
            }
        }

        if ($this->canUseSmtp()) {
            $ok = $this->sendSmtp($to, $subject, $body, $replyTo);
            if ($ok) {
                return true;
            }
        }
        $ok = $this->sendViaPhpMail($to, $subject, $body, $replyTo);
        if ($ok) {
            return true;
        }

        error_log('Email delivery failed: SendGrid, SMTP, and mail() all unavailable or failed.');
        return false;
    }

    private function canUseSendGrid()
    {
        return $this->sendgridApiKey !== '' && function_exists('curl_init');
    }

    private function parseEmailAddress($value)
    {
        if (preg_match('/<([^>]+)>/', $value, $matches)) {
            $email = trim($matches[1]);
            $name = trim(str_replace($matches[0], '', $value));
            $name = trim($name, '" ');

            return [
                'email' => $email,
                'name' => $name,
            ];
        }

        return [
            'email' => trim($value),
            'name' => '',
        ];
    }

    private function sendSendGrid($to, $subject, $body, $replyTo = null)
    {
        $curl = curl_init();

        $from = $this->parseEmailAddress($this->from);
        $toAddress = $this->parseEmailAddress($to);

        $payload = [
            'personalizations' => [[
                'to' => [[
                    'email' => $toAddress['email'],
                    'name' => $toAddress['name'] !== '' ? $toAddress['name'] : null,
                ]],
                'subject' => $subject,
            ]],
            'from' => [
                'email' => $from['email'],
                'name' => $from['name'] !== '' ? $from['name'] : null,
            ],
            'content' => [[
                'type' => 'text/html',
                'value' => $body,
            ]],
        ];

        if ($replyTo) {
            $replyToAddress = $this->parseEmailAddress($replyTo);
            $payload['reply_to'] = [
                'email' => $replyToAddress['email'],
                'name' => $replyToAddress['name'] !== '' ? $replyToAddress['name'] : null,
            ];
        }

        curl_setopt_array($curl, [
            CURLOPT_URL => SENDGRID_API_URL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->sendgridApiKey,
                'Content-Type: application/json'
            ],
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        error_log("SendGrid response code: {$httpCode}");
        
        if ($error) {
            error_log("SendGrid API error: {$error}");
            return false;
        }

        if ($httpCode !== 202) {
            error_log("SendGrid API HTTP {$httpCode}: {$response}");
            error_log("SendGrid payload: " . json_encode($payload));
            return false;
        }

        error_log("SendGrid success: Email sent to {$to}");
        return true;
    }

    private function canUseSmtp()
    {
        return $this->host !== '' && $this->user !== '' && $this->pass !== '';
    }

    private function sendSmtp($to, $subject, $body, $replyTo = null)
    {
        $secure = $this->encryption === 'ssl' || $this->port === 465;
        $transport = $secure ? 'ssl://' : '';
        $socket = @stream_socket_client($transport . $this->host . ':' . $this->port, $errno, $errstr, 20);
        if (!$socket) {
            error_log("SMTP connect failed: {$errno} {$errstr}");
            return false;
        }

        stream_set_timeout($socket, 20);
        if (!$this->smtpExpect($socket, [220])) return false;
        $hostName = $_SERVER['HTTP_HOST'] ?? 'localhost';
        if (!$this->smtpCommand($socket, "EHLO {$hostName}\r\n", [250])) return false;

        if (!$secure && $this->encryption === 'tls') {
            if (!$this->smtpCommand($socket, "STARTTLS\r\n", [220])) return false;
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) return false;
            if (!$this->smtpCommand($socket, "EHLO {$hostName}\r\n", [250])) return false;
        }

        if (!$this->smtpCommand($socket, "AUTH LOGIN\r\n", [334])) return false;
        if (!$this->smtpCommand($socket, base64_encode($this->user) . "\r\n", [334])) return false;
        if (!$this->smtpCommand($socket, base64_encode($this->pass) . "\r\n", [235])) return false;

        if (!$this->smtpCommand($socket, "MAIL FROM:<{$this->user}>\r\n", [250])) return false;
        if (!$this->smtpCommand($socket, "RCPT TO:<{$to}>\r\n", [250, 251])) return false;
        if (!$this->smtpCommand($socket, "DATA\r\n", [354])) return false;

        $headers = [];
        $headers[] = "From: {$this->from}";
        $headers[] = "To: {$to}";
        if ($replyTo) {
            $headers[] = "Reply-To: {$replyTo}";
        }
        $headers[] = "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=";
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-Type: text/html; charset=UTF-8";
        $headers[] = "Content-Transfer-Encoding: 8bit";

        $message = implode("\r\n", $headers) . "\r\n\r\n" . $body . "\r\n.\r\n";
        if (!$this->smtpCommand($socket, $message, [250])) return false;

        $this->smtpCommand($socket, "QUIT\r\n", [221]);
        fclose($socket);
        return true;
    }

    private function smtpCommand($socket, $command, $expectCodes)
    {
        fwrite($socket, $command);
        return $this->smtpExpect($socket, $expectCodes);
    }

    private function smtpExpect($socket, $expectCodes)
    {
        $response = '';
        while (($line = fgets($socket, 515)) !== false) {
            $response .= $line;
            if (strlen($line) >= 4 && $line[3] === ' ') {
                break;
            }
        }
        if ($response === '') {
            return false;
        }

        $code = (int)substr($response, 0, 3);
        if (!in_array($code, $expectCodes, true)) {
            error_log("SMTP unexpected response: {$response}");
            return false;
        }
        return true;
    }

    private function sendViaPhpMail($to, $subject, $body, $replyTo = null)
    {
        $headers = [];
        $headers[] = "From: {$this->from}";
        $headers[] = "Content-Type: text/html; charset=UTF-8";
        $headers[] = "MIME-Version: 1.0";

        if ($replyTo) {
            $headers[] = "Reply-To: {$replyTo}";
        }

        $headerString = implode("\r\n", $headers);
        
        $result = @mail($to, $subject, $body, $headerString);
        
        if ($result) {
            error_log("Email sent via PHP mail() function to: {$to}");
            return true;
        }
        
        error_log("PHP mail() failed for: {$to}");
        return false;
    }
}
    

/*

require_once dirname(__DIR__) . '/config.php';

class Mailer
{
    private $host;
    private $port;
    private $user;
    private $pass;
    private $encryption;
    private $from;

    public function __construct()
    {
        $this->host = SMTP_HOST;
        $this->port = SMTP_PORT;
        $this->user = SMTP_USER;
        $this->pass = SMTP_PASS;
        $this->encryption = SMTP_ENCRYPTION;
        $this->from = EMAIL_FROM;
    }

    public function send($to, $subject, $body, $replyTo = null)
    {
        if ($this->canUseSmtp()) {
            $ok = $this->sendSmtp($to, $subject, $body, $replyTo);
            if ($ok) {
                return true;
            }
        }

        return $this->sendMailFunction($to, $subject, $body, $replyTo);
    }

    private function canUseSmtp()
    {
        return $this->host !== '' && $this->user !== '' && $this->pass !== '';
    }

    private function sendMailFunction($to, $subject, $body, $replyTo = null)
    {
        $from = $this->from;
        $headers = [];
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-type: text/html; charset=UTF-8";
        $headers[] = "From: {$from}";
        if ($replyTo) {
            $headers[] = "Reply-To: {$replyTo}";
        }
        return mail($to, $subject, $body, implode("\r\n", $headers));
    }

    private function sendSmtp($to, $subject, $body, $replyTo = null)
    {
        $secure = $this->encryption === 'ssl' || $this->port === 465;
        $transport = $secure ? 'ssl://' : '';
        $socket = @stream_socket_client($transport . $this->host . ':' . $this->port, $errno, $errstr, 20);
        if (!$socket) {
            error_log("SMTP connect failed: {$errno} {$errstr}");
            return false;
        }

        stream_set_timeout($socket, 20);
        if (!$this->smtpExpect($socket, [220])) return false;
        $hostName = $_SERVER['HTTP_HOST'] ?? 'localhost';
        if (!$this->smtpCommand($socket, "EHLO {$hostName}\r\n", [250])) return false;

        if (!$secure && $this->encryption === 'tls') {
            if (!$this->smtpCommand($socket, "STARTTLS\r\n", [220])) return false;
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) return false;
            if (!$this->smtpCommand($socket, "EHLO {$hostName}\r\n", [250])) return false;
        }

        if (!$this->smtpCommand($socket, "AUTH LOGIN\r\n", [334])) return false;
        if (!$this->smtpCommand($socket, base64_encode($this->user) . "\r\n", [334])) return false;
        if (!$this->smtpCommand($socket, base64_encode($this->pass) . "\r\n", [235])) return false;

        if (!$this->smtpCommand($socket, "MAIL FROM:<{$this->user}>\r\n", [250])) return false;
        if (!$this->smtpCommand($socket, "RCPT TO:<{$to}>\r\n", [250, 251])) return false;
        if (!$this->smtpCommand($socket, "DATA\r\n", [354])) return false;

        $headers = [];
        $headers[] = "From: {$this->from}";
        $headers[] = "To: {$to}";
        if ($replyTo) {
            $headers[] = "Reply-To: {$replyTo}";
        }
        $headers[] = "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=";
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-Type: text/html; charset=UTF-8";
        $headers[] = "Content-Transfer-Encoding: 8bit";

        $message = implode("\r\n", $headers) . "\r\n\r\n" . $body . "\r\n.\r\n";
        if (!$this->smtpCommand($socket, $message, [250])) return false;

        $this->smtpCommand($socket, "QUIT\r\n", [221]);
        fclose($socket);
        return true;
    }

    private function smtpCommand($socket, $command, $expectCodes)
    {
        fwrite($socket, $command);
        return $this->smtpExpect($socket, $expectCodes);
    }

    private function smtpExpect($socket, $expectCodes)
    {
        $response = '';
        while (($line = fgets($socket, 515)) !== false) {
            $response .= $line;
            if (strlen($line) >= 4 && $line[3] === ' ') {
                break;
            }
        }
        if ($response === '') {
            return false;
        }

        $code = (int)substr($response, 0, 3);
        if (!in_array($code, $expectCodes, true)) {
            error_log("SMTP unexpected response: {$response}");
            return false;
        }
        return true;
    }
}

*/