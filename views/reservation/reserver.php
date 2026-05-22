<?php
$page_title = "Reserver";
require_once '../../config.php';
require_once '../../controllers/VoitureController.php';
require_once '../../controllers/ReservationController.php';

$voiture_id = (int)($_GET['id'] ?? 0);
$voiture = (new VoitureController())->getById($voiture_id);
if (!$voiture) {
    setFlash('Vehicule introuvable', 'error');
    redirect(SITE_URL);
}

$reservationController = new ReservationController();
$bookedDateRanges = $reservationController->getBookedDateRanges($voiture_id);
$disabledDates = [];

foreach ($bookedDateRanges as $range) {
    try {
        $current = new DateTime($range['date_debut']);
        $end = new DateTime($range['date_fin']);
        while ($current <= $end) {
            $disabledDates[] = $current->format('Y-m-d');
            $current->modify('+1 day');
        }
    } catch (Exception $e) {
        
    }
}

$disabledDates = array_values(array_unique($disabledDates));

$error = $success = '';
$is_logged = isLogged();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_logged) {
    $result = (new ReservationController())->create(
        $_SESSION['user_id'],
        $voiture_id,
        $_POST['date_debut'],
        $_POST['date_fin'],
        $_POST['lieuPriseEnCharge'],
        $_POST['lieuRetour']
    );

    if ($result['success']) {
        $success = 'Reservation effectuee !';
        header('refresh:2;url=../client/reservations.php');
    } else {
        $error = $result['message'];
    }
}

require_once '../../includes/header.php';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<div class="reservation-container">
    <h1>Reserver <?= htmlspecialchars($voiture['modele']) ?></h1>

    <div class="reservation-grid">
        <div class="reservation-card vehicle-card">
            <div class="vehicle-image-wrapper">
                <button type="button" class="vehicle-image-button" id="vehicle-image-button" aria-label="Agrandir la photo du véhicule">
                    <img
                        src="<?= htmlspecialchars(vehicle_image_src($voiture['image_url'] ?? '')) ?>"
                        alt="<?= htmlspecialchars($voiture['modele']) ?>"
                        class="vehicle-image"
                        id="vehicle-image"
                        onerror="this.src='<?= SITE_URL ?>assets/images/no-car-placeholder.svg'"
                    >
                </button>
                
            </div>

            <?php if ($is_logged): ?>
            <div class="vehicle-content">
                <h2 class="vehicle-title"><?= htmlspecialchars($voiture['modele']) ?></h2>

                <div class="deals-card-grid reservation-vehicle-grid">
                    <div class="reservation-vehicle-stat">
                        <span><i class="ri-settings-3-line"></i></span>
                        <div>
                            <small>Carburant</small>
                            <strong><?= htmlspecialchars($voiture['carburant'] ?? 'Non specifie') ?></strong>
                        </div>
                    </div>

                    <div class="reservation-vehicle-stat">
                        <span><i class="ri-user-3-line"></i></span>
                        <div>
                            <small>Places</small>
                            <strong><?= (int)($voiture['places'] ?? 0) ?> places</strong>
                        </div>
                    </div>

                    <div class="reservation-vehicle-stat">
                        <span><i class="ri-speed-up-line"></i></span>
                        <div>
                            <small>Kilometrage</small>
                            <strong><?= number_format((float)($voiture['kilometrage'] ?? 0), 0) ?> km</strong>
                        </div>
                    </div>

                    <div class="reservation-vehicle-stat">
                        <span><i class="ri-price-tag-3-line"></i></span>
                        <div>
                            <small>Caution</small>
                            <strong><?= number_format((float)($voiture['caution'] ?? 0), 0) ?> DA</strong>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="reservation-card reservation-form-card">
            <div class="card-body">
                <h3>Informations de reservation</h3>

                <?php if ($error): ?>
                    <div class="reservation-alert error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="reservation-alert success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <?php if (!$is_logged): ?>
                    <div class="vehicle-details">
                        <div class="vehicle-detail">
                            <span>Voiture :</span>
                            <strong><?= htmlspecialchars($voiture['modele']) ?></strong>
                        </div>
                        <div class="vehicle-detail">
                            <span>Carburant :</span>
                            <strong><?= htmlspecialchars($voiture['carburant'] ?? 'Non specifie') ?></strong>
                        </div>
                        <div class="vehicle-detail">
                            <span>Places :</span>
                            <strong><?= (int)($voiture['places'] ?? 0) ?> places</strong>
                        </div>
                        <div class="vehicle-detail">
                            <span>Kilometrage :</span>
                            <strong><?= number_format((float)($voiture['kilometrage'] ?? 0), 0) ?> km</strong>
                        </div>
                        <div class="vehicle-detail">
                            <span>Caution :</span>
                            <strong><?= number_format((float)($voiture['caution'] ?? 0), 0) ?> DA</strong>
                        </div>
                    </div>
                    <div class="auth-message">
                        <p>Connectez-vous pour reserver</p>
                        <a href="<?= SITE_URL ?>views/auth/login.php" class="btn">Se connecter</a>
                        <a href="<?= SITE_URL ?>views/auth/login.php?tab=register" class="register-link">Creer un compte</a>
                    </div>
                <?php else: ?>
                    <form method="POST" class="reservation-form">
                        <div class="date">
                            <div class="form-group">
                                <label for="date_debut">Date de debut</label>
                                <input type="text" id="date_debut" name="date_debut" class="reservation-date-input" placeholder="jj/mm/aaaa" autocomplete="off" required>
                            </div>

                            <div class="form-group">
                                <label for="date_fin">Date de fin</label>
                                <input type="text" id="date_fin" name="date_fin" class="reservation-date-input" placeholder="jj/mm/aaaa" autocomplete="off" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="lieuPriseEnCharge">Lieu de prise en charge</label>
                            <input type="text" id="lieuPriseEnCharge" name="lieuPriseEnCharge" placeholder="Ex: Agence Alger Centre" required>
                        </div>

                        <div class="form-group">
                            <label for="lieuRetour">Lieu de retour</label>
                            <input type="text" id="lieuRetour" name="lieuRetour" placeholder="Ex: Aeroport d'Alger" required>
                        </div>

                        <div class="reservation-summary">
                            <h4>Resume</h4>

                            <div>
                                <span>Voiture :</span>
                                <strong><?= htmlspecialchars($voiture['modele']) ?></strong>
                            </div>

                            <div>
                                <span>Prix / jour :</span>
                                <strong><?= number_format((float)($voiture['prix_jour'] ?? 0), 0) ?> DA</strong>
                            </div>

                            <div>
                                <span>Nombre de jours :</span>
                                <strong id="nb-jours">0</strong>
                            </div>

                            <div>
                                <span>Prix total :</span>
                                <strong id="prix-total">0 DA</strong>
                            </div>
                        </div>

                        <button type="submit">Confirmer la reservation</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="vehicle-photo-modal" id="vehicle-photo-modal" aria-hidden="true">
    <div class="vehicle-photo-modal__content">
        <button type="button" class="vehicle-photo-modal__close" id="vehicle-photo-close" aria-label="Fermer la photo">&times;</button>
        <img
            src="<?= htmlspecialchars(vehicle_image_src($voiture['image_url'] ?? '')) ?>"
            alt="<?= htmlspecialchars($voiture['modele']) ?>"
            class="vehicle-photo-modal__img"
            id="vehicle-photo-modal-img"
            onerror="this.src='<?= SITE_URL ?>assets/images/no-car-placeholder.svg'"
        >
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const dateDebut = document.getElementById('date_debut');
    const dateFin = document.getElementById('date_fin');
    const nbJoursEl = document.getElementById('nb-jours');
    const prixTotalEl = document.getElementById('prix-total');
    const vehicleImageButton = document.getElementById('vehicle-image-button');
    const vehiclePhotoModal = document.getElementById('vehicle-photo-modal');
    const vehiclePhotoClose = document.getElementById('vehicle-photo-close');
    const prixJour = <?= json_encode((float)($voiture['prix_jour'] ?? 0)) ?>;
    const disabledDates = <?= json_encode($disabledDates, JSON_UNESCAPED_SLASHES) ?>;

    if (window.flatpickr && dateDebut && dateFin) {
        const commonOptions = {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd/m/Y',
            minDate: 'today',
            disable: disabledDates,
            allowInput: false,
            disableMobile: true,
            locale: {
                firstDayOfWeek: 1
            },
            onChange: function (selectedDates, dateStr, instance) {
                if (instance.input.id === 'date_debut' && dateStr) {
                    dateFin._flatpickr.set('minDate', dateStr);
                    if (dateFin.value && dateFin.value < dateStr) {
                        dateFin._flatpickr.clear();
                    }
                }

                calculerTotal();
            }
        };

        flatpickr(dateDebut, commonOptions);
        flatpickr(dateFin, commonOptions);
    }

    if (!dateDebut || !dateFin || !nbJoursEl || !prixTotalEl) {
        return;
    }

    function formatDA(value) {
        return new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(value) + ' DA';
    }

    function calculerTotal() {
        const debutValue = dateDebut.value;
        const finValue = dateFin.value;

        if (!debutValue || !finValue) {
            nbJoursEl.textContent = '0';
            prixTotalEl.textContent = formatDA(0);
            return;
        }

        const debut = new Date(debutValue + 'T00:00:00');
        const fin = new Date(finValue + 'T00:00:00');
        const diffMs = fin.getTime() - debut.getTime();
        const jours = Math.ceil(diffMs / (1000 * 60 * 60 * 24));

        if (jours <= 0) {
            nbJoursEl.textContent = '0';
            prixTotalEl.textContent = formatDA(0);
            return;
        }

        nbJoursEl.textContent = String(jours);
        prixTotalEl.textContent = formatDA(jours * prixJour);
    }

    dateDebut.addEventListener('change', function () {
        if (dateDebut.value) {
            dateFin.min = dateDebut.value;
        }
        calculerTotal();
    });

    dateFin.addEventListener('change', calculerTotal);
    calculerTotal();

    function openPhotoModal() {
        if (!vehiclePhotoModal) {
            return;
        }

        vehiclePhotoModal.classList.add('is-open');
        vehiclePhotoModal.setAttribute('aria-hidden', 'false');
    }

    function closePhotoModal() {
        if (!vehiclePhotoModal) {
            return;
        }

        vehiclePhotoModal.classList.remove('is-open');
        vehiclePhotoModal.setAttribute('aria-hidden', 'true');
    }

    if (vehicleImageButton) {
        vehicleImageButton.addEventListener('click', openPhotoModal);
    }

    if (vehiclePhotoClose) {
        vehiclePhotoClose.addEventListener('click', closePhotoModal);
    }

    if (vehiclePhotoModal) {
        vehiclePhotoModal.addEventListener('click', function (event) {
            if (event.target === vehiclePhotoModal) {
                closePhotoModal();
            }
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closePhotoModal();
        }
    });

    if (!dateDebut || !dateFin || !nbJoursEl || !prixTotalEl) {
        return;
    }

    function formatDA(value) {
        return new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(value) + ' DA';
    }

    function calculerTotal() {
        const debutValue = dateDebut.value;
        const finValue = dateFin.value;

        if (!debutValue || !finValue) {
            nbJoursEl.textContent = '0';
            prixTotalEl.textContent = formatDA(0);
            return;
        }

        const debut = new Date(debutValue + 'T00:00:00');
        const fin = new Date(finValue + 'T00:00:00');
        const diffMs = fin.getTime() - debut.getTime();
        const jours = Math.ceil(diffMs / (1000 * 60 * 60 * 24));

        if (jours <= 0) {
            nbJoursEl.textContent = '0';
            prixTotalEl.textContent = formatDA(0);
            return;
        }

        nbJoursEl.textContent = String(jours);
        prixTotalEl.textContent = formatDA(jours * prixJour);
    }

    dateDebut.addEventListener('change', function () {
        if (dateDebut.value) {
            dateFin.min = dateDebut.value;
        }
        calculerTotal();
    });

    dateFin.addEventListener('change', calculerTotal);
    calculerTotal();
});
</script>

<style>
.reservation-date-input + .flatpickr-input,
.reservation-date-input {
    background: #ffffff !important;
    color: #0f172a;
}

.flatpickr-calendar {
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 22px 60px rgba(15, 23, 42, 0.18);
}

.flatpickr-day {
    border-radius: 10px;
    color: #2563eb;
    background: #eaf2ff;
    border: 1px solid #dbeafe;
    font-weight: 600;
}

.flatpickr-day:hover,
.flatpickr-day:focus {
    background: #dbeafe;
    border-color: #93c5fd;
}

.flatpickr-day.selected,
.flatpickr-day.selected:hover,
.flatpickr-day.selected:focus {
    background: #ffffff;
    border-color: #2563eb;
    color: #2563eb;
    box-shadow: inset 0 0 0 2px #2563eb;
}

.flatpickr-day.disabled,
.flatpickr-day.disabled:hover {
    background: #f1f5f9;
    border-color: #e2e8f0;
    color: #94a3b8;
    cursor: not-allowed;
    box-shadow: none;
    opacity: 0.8;
}

.flatpickr-day.disabled::after {
    content: '';
    display: block;
}

.vehicle-details {
    margin-bottom: 20px;
}

.vehicle-detail {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #e2e8f0;
}

.vehicle-detail:last-child {
    border-bottom: none;
}

.vehicle-detail span {
    font-weight: 500;
    color: #64748b;
}

.vehicle-detail strong {
    color: #0f172a;
}

.vehicle-photo-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.vehicle-photo-modal.is-open {
    display: flex;
}

.vehicle-photo-modal__content {
    position: relative;
    max-width: 90%;
    max-height: 90%;
    background: #fff;
    border-radius: 8px;
    padding: 10px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.vehicle-photo-modal__img {
    max-width: 100%;
    max-height: 100%;
    border-radius: 4px;
}

.vehicle-photo-modal__close {
    position: absolute;
    top: -15px;
    right: -15px;
    background: #fff;
    border: 2px solid #e2e8f0;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    cursor: pointer;
    font-size: 24px;
    line-height: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #64748b;
    transition: all 0.2s ease;
}

.vehicle-photo-modal__close:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
    color: #334155;
}
</style>

<?php require_once '../../includes/footer.php'; ?>
