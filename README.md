# Medux Wallet

Crypto portfolio management application with real-time price tracking and blockchain explorer.

## Stack

- Frontend: Vue.js 3, Vite, Pinia, Vue Router, Lucide Icons
- Backend: Symfony 6.4, Doctrine ORM, JWT Authentication
- Database: MySQL 8
- APIs: CoinGecko (crypto prices), Blockchain.com (BTC explorer)

## Features

- User authentication with JWT tokens
- Portfolio management (CRUD operations)
- Real-time crypto prices with automatic refresh
- Profit/Loss calculations
- Bitcoin blockchain explorer
- Responsive design with animations

## Installation

### Prerequisites

- PHP 8.1+
- Composer
- Node.js 18+
- MySQL 8+

### Backend Setup

```bash
cd backend
composer install
cp .env .env.local
# Edit .env.local with your database credentials
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console lexik:jwt:generate-keypair
php -S localhost:8080 -t public
```

### Frontend Setup

```bash
cd frontend
npm install
npm run dev
```

## Configuration

### Backend (.env.local)

```env
DATABASE_URL="mysql://root:@127.0.0.1:3306/medux_wallet?serverVersion=8.0.32&charset=utf8mb4"
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=your_secure_passphrase
```

### Frontend (.env)

```env
VITE_API_URL=http://localhost:8080
```

## Project Structure

```
backend/
├── src/
│   ├── Controller/        # API endpoints
│   ├── Entity/            # Doctrine entities
│   ├── Repository/        # Data repositories
│   └── Service/           # Business logic
├── config/
│   ├── packages/          # Bundle configuration
│   └── routes/            # Routing configuration

frontend/
├── src/
│   ├── views/             # Page components
│   ├── stores/            # Pinia state management
│   ├── services/          # API services
│   ├── router/            # Route definitions
│   └── assets/            # Styles and theme
```

## API Endpoints

### Authentication
- POST `/api/register` - Create new user
- POST `/api/login` - Authenticate user
- GET `/api/me` - Get current user

### Portfolio
- GET `/api/portfolio` - List user assets
- POST `/api/portfolio` - Add asset
- PUT `/api/portfolio/{id}` - Update asset
- DELETE `/api/portfolio/{id}` - Remove asset

### Crypto Data
- GET `/api/crypto/prices?ids=bitcoin,ethereum` - Get prices
- GET `/api/crypto/detailed?ids=bitcoin` - Get detailed data
- GET `/api/crypto/search?q=bitcoin` - Search coins
- GET `/api/crypto/top?limit=20` - Top cryptocurrencies

### Blockchain
- GET `/api/blockchain/blocks?limit=10` - Latest blocks
- GET `/api/blockchain/latest` - Latest block
- GET `/api/blockchain/transactions/unconfirmed` - Mempool
- GET `/api/blockchain/stats` - Network statistics

## Development Notes

### State Management

- `authStore`: User authentication state
- `portfolioStore`: Portfolio data with computed values

### Services Layer

- `authService`: Authentication API calls
- `portfolioService`: Portfolio CRUD operations
- `cryptoService`: CoinGecko API integration
- `blockchainService`: Blockchain.com API integration

### Key Components

- `DashboardPage`: Main portfolio view with stats and asset cards
- `BlockchainPage`: Bitcoin blockchain explorer with tabs
- `LoginPage` / `RegisterPage`: Authentication forms

### Styling

- Theme: Orange (#ff6b35) and white
- CSS variables in `assets/theme.css`
- Icon styles in `assets/icons.css`
- Component-scoped styles

## Security

- JWT tokens stored in localStorage
- Password hashing with Symfony's PasswordHasher
- CORS configured for localhost development
- API routes protected by JWT firewall

## License

MIT
