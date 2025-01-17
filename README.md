# Inventory Tracker Application

Developed by Lucas Valenca

A robust inventory management system built with CakePHP 5, featuring comprehensive product tracking, status management, and inventory control.

## System Requirements

- PHP >= 8.1
- MySQL >= 8.0
- Composer >= 2.0
- Node.js >= 16.0 (for asset compilation)
- Required PHP Extensions:
  - intl
  - mbstring
  - json
  - pdo_mysql

## Features

### Core Functionality

- Product Management (CRUD operations)
- Automated Status Calculation
- Stock Level Monitoring
- Search and Filter Capabilities
- Audit Trail for Changes

### Security Features

- CSRF Protection
- XSS Prevention
- Input Validation
- Role-based Access Control
- SQL Injection Prevention

## Project Structure

```
inventory-tracker
├── src
│   ├── Controller
│   │   └── ProductsController.php
│   ├── Model
│   │   ├── Entity
│   │   │   └── Product.php
│   │   └── Table
│   │       └── ProductsTable.php
│   ├── Template
│   │   └── Products
│   │       ├── index.php
│   │       ├── view.php
│   │       ├── add.php
│   │       └── edit.php
│   └── View
│       └── AppView.php
├── config
│   ├── app.php
│   ├── bootstrap.php
│   └── routes.php
├── logs
├── tmp
├── vendor
├── composer.json
└── composer.lock
```

## Installation

1. Clone the repository:

   ```
   git clone https://github.com/lucasvalenca/inventory-tracker.git
   cd inventory-tracker
   ```

2. Install dependencies:

composer install
npm install

3. Configure environment:
   cp config/.env.example config/.env

4. Initialize database:
   bin/cake migrations migrate
   bin/cake migrations seed

5. Start development server:

bin/cake server

## Testing

Run the test suite:
composer test

Static analysis:
composer stan
composer psalm

Code style checks:

## Security Considerations

- Always update dependencies regularly
- Configure proper file permissions
- Use environment variables for sensitive data
- Enable HTTPS in production
- Implement rate limiting
- Regular security audits

## Performance Optimization

- Enable OPcache in production
- Configure proper caching
- Optimize database queries
- Use proper indexing
- Enable compression

## Deployment

1. Production environment setup:
   composer install --no-dev --optimize-autoloader

2. Security checks:
   composer audit

3. Cache configuration:
   bin/cake cache clear_all

   Contributing

4. Fork the repository
5. Create feature branch
6. Commit changes
7. Push to branch
8. Create Pull Request

## License

Proprietary - All rights reserved

## Support

For issues and feature requests, please use the GitHub issue tracker.

## Usage

- Navigate to the Products section to manage your inventory.
- Use the search box to filter products by name.
- Add new products or edit existing ones as needed.
