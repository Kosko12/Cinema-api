# Cinema REST API

A REST API dla zarządzania kinem, zbudowane w **Symfony 6.4** oraz **PHP 8.0+**.

## Funkcje

- **Endpointy konsumenta** (Public Access):
  - GET `/api/rooms` - Pobiera wszystkie sale kinowe z dostępnością miejsc
  - POST `/api/reservations` - Rezerwacja miejsca

- **Endpointy pracownika** (JWT Authentication Wymagane):
  - POST `/api/rooms` - Utwórz nową salę kinową
  - PUT `/api/rooms/{id}` - Aktualizacja istniejącej sali
  - DELETE `/api/rooms/{id}` - Usunięcie sali

## Wymagania

- PHP >= 8.0
- MySQL 8.0 (or Docker + Docker Compose)
- Composer

## Szybki start

### Automatyczna instalacja (zalecana)

Uruchom skrypt instalacyjny:

```bash
chmod +x setup.sh
./setup.sh
```

Ten skrypt:
- ✅ Sprawdza instalację Dockera
- ✅ Instaluje zależności Composer
- ✅ Uruchamia kontenery Docker (MySQL)
- ✅ Czeka na gotowość MySQL
- ✅ Tworzy bazę danych
- ✅ Uruchamia migracje
- ✅ Generuje klucze JWT
- ✅ Tworzy użytkownika pracownika

Po zakończeniu instalacji, uruchom serwer:
```bash
php -S localhost:8000 -t public
```

### Instalacja manualna

#### Option 1: Użycie Docker'a (Rekomendowane)

1. **Instalacja zależności:**
```bash
composer install
```

2. **Uruchomienie serwera MySQL:**
```bash
docker compose up -d
```

3. **Tworzenie bazy danych:**
```bash
php bin/console doctrine:database:create
```

4. **Uruchomienie migracji:**
```bash
php bin/console doctrine:migrations:migrate
```

5. **Generowanie kluczy JWT:**
```bash
mkdir -p config/jwt
openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096 -pass pass:changeme
openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout -passin pass:changeme
```

6. **Tworzenie pracownika kina:**
```bash
php bin/console app:create-employee employee@cinema.com password123
```

#### Option 2: Użycie MySQL lokalnie

1. **Instalacja zależności:**
```bash
composer install
```

2. **Konfiguracja bazy danych:**
Edytuj plik `.env` i odkomentuj linię z lokalną MySQL:
```
DATABASE_URL="mysql://root:password@127.0.0.1:3306/cinema_db?serverVersion=8.0&charset=utf8mb4"
```

3. **Tworzenie bazy danych:**
```bash
php bin/console doctrine:database:create
```

4. **Uruchomienie migracji:**
```bash
php bin/console doctrine:migrations:migrate
```

5. **Generowanie kluczy JWT:**
```bash
mkdir -p config/jwt
openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096 -pass pass:changeme
openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout -passin pass:changeme
```

6. **Tworzenie pracownika kina:**
```bash
php bin/console app:create-employee employee@cinema.com password123
```

## Uruchomienie aplikacji

Uruchom serwer Symfony:
```bash
symfony server:start
```

lub użyj wbudowanego serwera PHP:
```bash
php -S localhost:8000 -t public
```

## Przykłady użycia API

### 1. Pobranie sal (Public)
```bash
curl -X GET http://localhost:8000/api/rooms
```

### 2. Utworzenie sali (Wymaga JWT)

Pierwsze, zaloguj się, aby uzyskać token JWT:
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"employee@cinema.com","password":"your_password"}'
```

Następnie użyj tokenu do utworzenia sali:
```bash
curl -X POST http://localhost:8000/api/rooms \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -d '{"name":"Cinema Hall 1","rows":10,"seatsPerRow":15}'
```

### 3. Aktualizacja sali (Wymaga JWT)
```bash
curl -X PUT http://localhost:8000/api/rooms/1 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -d '{"name":"Updated Hall","rows":12,"seatsPerRow":20}'
```

### 4. Usunięcie sali (Wymaga JWT)
```bash
curl -X DELETE http://localhost:8000/api/rooms/1 \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

### 5. Rezerwacja miejsca (Public)
```bash
curl -X POST http://localhost:8000/api/reservations \
  -H "Content-Type: application/json" \
  -d '{"roomId":1,"row":5,"seat":10,"customerEmail":"customer@example.com"}'
```

## Uruchomienie testów

### Testy jednostkowe
```bash
php bin/phpunit tests/Unit
```

### Testy integracyjne
```bash
php bin/phpunit tests/Integration
```

### Wszystkie testy
```bash
php bin/phpunit
```

## Struktura projektu

```
cinema_rest_api/
├── bin/                    # Console and PHPUnit executables
├── config/                 # Configuration files
│   ├── packages/          # Bundle configurations
│   └── routes.yaml        # Routing configuration
├── migrations/            # Database migrations
├── public/                # Web root
│   └── index.php         # Front controller
├── src/
│   ├── Controller/       # API Controllers
│   ├── Entity/           # Doctrine entities
│   ├── Repository/       # Database repositories
│   └── Service/          # Business logic
├── tests/
│   ├── Unit/             # Unit tests
│   └── Integration/      # Integration tests
├── .env                  # Environment configuration
└── composer.json         # Dependencies
```

## Bezpieczeństwo

- Pracownicze endpointy są chronione autoryzacją JWT
- Hasła są hashowane za pomocą bcrypt
- CORS jest skonfigurowany do dostępu do API
- Walidacja danych jest wykonywana na wszystkich endpointach

## Testy

- **Testy jednostkowe**: Testy dla `RoomService` w tym:
  - `generateSeatsForRoom()` - funkcja generowania miejsc
  - `calculateAvailableSeats()` - funkcja obliczania dostępności

- **Testy integracyjne**: Testy pełnego endpointu dla:
  - zarządzania salami (GET, POST, PUT, DELETE)
  - rezerwacji miejsc
