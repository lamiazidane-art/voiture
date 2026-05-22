FROM php:8.4-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions for MySQL and cURL
RUN docker-php-ext-install pdo_mysql mysqli

WORKDIR /app

# Copy application code
COPY . .

EXPOSE 8080

CMD ["php", "-S", "0.0.0.0:8080", "-t", "/app"]
