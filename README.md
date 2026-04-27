# Sushinelli (Laravel)

Web de empresa + menú + reservas.

## Requisitos
- PHP 8.2+
- Composer
- Node 18+
- MySQL

## Instalación rápida
```bash
composer install
cp .env.example .env
php artisan key:generate

# Configura DB_* en .env
php artisan migrate --seed

npm install
npm run dev
```

> Nota: la pantalla de **Reservas** utiliza un calendario visual (Flatpickr). Se instala automáticamente con `npm install`.

## Reservas
- Ruta: `/reservas` (requiere login)
- Seleccionas fecha + turno (comida/cena) y clicas una mesa.
- Mesas libres: **azul acero** (#4682B4) · ocupadas: **rojo**.

Extras implementados:
- Modal de confirmación al reservar.
- Cancelación de reservas propias (solo hoy o futuras) con modal.

## Emails
Configura `MAIL_*` en `.env` (por ejemplo Mailtrap o SMTP real).
- Al registrarse: email de bienvenida.
- Al reservar: email de confirmación.
