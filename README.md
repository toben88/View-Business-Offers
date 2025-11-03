# Offer Comparison Tool v2.02

A production-ready web application for business sellers to compare multiple acquisition offers side-by-side. Features interactive visualizations, seller note tracking with balloon payments, and persistent data storage.

## Features

- **Multi-Offer Comparison**: Compare unlimited offers simultaneously with side-by-side metrics
- **Interactive Cash Flow Timeline**: Visualize payment schedules over 10 years with Chart.js
- **Seller Note Support**: Track seller financing with interest rates, durations, and balloon payment options
- **Save & Load Comparisons**: Persist comparison scenarios to SQLite database
- **Auto-Load**: Automatically loads most recent comparison on page load
- **Manage Saved Comparisons**: View, load, and delete saved comparison scenarios
- **Production Security**: Rate limiting, input validation, security headers, and CSRF protection
- **Fully Portable**: All relative paths - deploy to any directory or domain

## Screenshots

- Quick Comparison Table: Purchase price, cash at closing, seller note terms, 5-year and 10-year totals
- Cash Flow Timeline Graph: Cumulative cash received over time with balloon payment visualization
- Detailed Offer Cards: Complete breakdown of each offer with monthly payment calculations

## File Structure

```
buyeroffers/
├── index.php         # Main application (v2.02)
├── database.php           # SQLite database handler
├── data/                  # Database storage directory (auto-created)
│   ├── .htaccess         # Blocks direct web access to database
│   └── offers.db         # SQLite database (auto-created)
├── .gitignore            # Excludes database and backup files
├── LICENSE               # CC BY-NC-SA 4.0 License
└── README.md             # This file
```

## Installation

### Prerequisites
- PHP 7.4 or higher
- PDO SQLite extension (usually included with PHP)
- Apache or Nginx web server (or PHP built-in server for development)
- HTTPS enabled on production server

### Steps

1. **Upload Files**: Upload the entire `buyeroffers` folder to your web server
2. **Set Permissions**: Configure file permissions (see Security section below)
3. **Verify .htaccess**: Ensure `.htaccess` file exists in `data/` directory to block direct access
4. **Access Application**: Navigate to `https://yourserver.com/buyeroffers/index.php`
5. **Test**: Add a test offer and verify database creation in `data/offers.db`

### Development Setup

```bash
# Navigate to the buyeroffers directory
cd /path/to/buyeroffers

# Start PHP built-in server (development only)
php -S localhost:8000

# Access at: http://localhost:8000/index.php
```

## Security Features (Production Ready)

### Implemented Protections
- ✅ **Session Security**: HTTPOnly cookies, SameSite=Strict, strict session mode
- ✅ **Rate Limiting**: 50 POST requests per hour per session
- ✅ **Input Validation**: Server-side validation with sensible limits
  - Purchase Price: $1 to $10 billion
  - Down Payment: $0 to purchase price
  - Seller Note Rate: 0% to 50%
  - Seller Note Duration: 1 to 480 months
  - Text fields: Character limits enforced
- ✅ **CSRF Protection**: Token validation on all form submissions
- ✅ **Security Headers**: X-Frame-Options, X-Content-Type-Options, CSP, XSS-Protection
- ✅ **Database Protection**: `.htaccess` blocks direct web access to `data/` directory
- ✅ **Error Handling**: Generic error messages to users, detailed logs for admins
- ✅ **SQL Injection Protection**: PDO prepared statements throughout

### Recommended Server Permissions

```bash
# Directories
chmod 0750 buyeroffers/
chmod 0750 buyeroffers/data/

# PHP Files
chmod 0644 buyeroffers/*.php

# Database Files (after creation)
chmod 0600 buyeroffers/data/offers.db

# Configuration Files
chmod 0644 buyeroffers/data/.htaccess
```

### Production Deployment Checklist

Before deploying to production:
- [ ] HTTPS is enabled and enforced
- [ ] File permissions are set correctly
- [ ] `.htaccess` is present in `data/` directory
- [ ] Backup files (`*.backup.php`) are not deployed
- [ ] `display_errors` is disabled (set to `0` in production)
- [ ] Error logging is configured
- [ ] Regular database backups are scheduled

## Usage

### Adding an Offer
1. Fill in buyer name (or leave blank for default)
2. Enter purchase price, down payment, and seller note amount
3. Set seller note interest rate and duration in months
4. Optionally enable balloon payment and specify year
5. Add any contingencies or notes
6. Click "Add Offer to Comparison"

### Comparing Offers
- **Quick Comparison Table**: Side-by-side metrics for all offers
- **Cash Flow Timeline**: Interactive graph showing cumulative cash received
- **Detailed Cards**: Expandable cards with full offer breakdown
- **Best Offer Badge**: Automatically highlights highest total value (10-year)

### Saving & Loading
- **Save**: Enter a comparison name and click "Save" to persist to database
- **Load**: Select from dropdown of saved comparisons and click "Load"
- **Manage**: View all saved comparisons with timestamps, load any, or delete unwanted ones
- **Auto-Load**: Most recent comparison loads automatically on page visit

### Editing & Deleting
- **Edit**: Click "Edit" button on any offer card to modify details
- **Delete**: Click "Delete" button to remove an offer
- **Clear All**: Remove all offers from current comparison

## Input Validation Limits

| Field | Minimum | Maximum | Notes |
|-------|---------|---------|-------|
| Purchase Price | $1 | $10,000,000,000 | 10 billion |
| Down Payment | $0 | Purchase Price | |
| Seller Note Amount | $0 | Purchase Price | Allows 0% interest |
| Seller Note Rate | 0% | 50% | |
| Seller Note Duration | 1 month | 480 months | 40 years |
| Balloon Year | 1 year | 30 years | |
| Buyer Name | - | 255 characters | |
| Contingencies | - | 5,000 characters | |
| Comparison Name | - | 255 characters | |

## Database Schema

### `offer_comparisons` Table
- `id`: Primary key
- `name`: Comparison name
- `created_at`: Creation timestamp
- `updated_at`: Last modified timestamp

### `offers` Table
- `id`: Primary key
- `comparison_id`: Foreign key to offer_comparisons
- `buyer_name`: Buyer/offer label
- `purchase_price`: Total purchase price
- `down_payment`: Cash at closing
- `seller_note_amount`: Amount financed by seller
- `seller_note_rate`: Annual interest rate
- `seller_note_duration`: Loan term in months
- `has_balloon`: Boolean flag for balloon payment
- `balloon_year`: Year balloon payment is due
- `contingencies`: Notes and contingencies text
- `created_at`: Creation timestamp

## Technology Stack

- **Backend**: PHP 7.4+ with PDO
- **Database**: SQLite 3
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Charting**: Chart.js v4.4.0
- **Fonts**: Google Fonts (Inter)
- **Security**: Native PHP session management, CSRF tokens

## Browser Compatibility

- Chrome/Edge 90+
- Firefox 88+
- Safari 14+
- Mobile browsers (iOS Safari, Chrome Mobile)

## Troubleshooting

### Database creation fails
- Ensure `buyeroffers/` directory is writable by web server
- Check PHP has SQLite extension enabled: `php -m | grep sqlite`
- Verify `data/` directory can be created

### Rate limit errors
- Session-based rate limiting: 50 POST requests per hour
- Clear browser cookies to reset session
- Wait 1 hour for automatic reset

### Validation errors with default values
- Empty fields use defaults and bypass validation
- Only filled fields are validated
- Defaults: $1M purchase, $750K down, $250K note, 7% rate, 120 months

### Charts not displaying
- Verify Chart.js CDN is accessible
- Check browser console for JavaScript errors
- Ensure `unsafe-inline` is in CSP script-src (development only)

## Version History

### v2.02 (Current)
- **Fixed Clear All button**: Resolved issue where offers were immediately reloaded after clearing
- **Fixed all redirect paths**: All redirects now use `$_SERVER['PHP_SELF']` for proper portability
- **Professional chart colors**: Updated timeline graph to use business-appropriate color palette
- **Cleaner UI**: Removed emoji icons from section headings for more professional appearance
- Fixed "Cancel" edit links to work correctly in all directory configurations

### v2.01
- **Auto-save functionality**: Automatically saves to database after every add/edit/delete operation
- **Cross-browser/computer sync**: Always loads most recent comparison from database on page load
- **Fixed edit button**: Resolved issue with editing offers loaded from database
- **Renamed to index.php**: Changed main file from viewoffers.php to index.php
- All browsers and devices now see synchronized data in real-time

### v2.0
- Added database persistence with SQLite
- Implemented save/load/manage comparisons
- Added auto-load of most recent comparison
- Production security hardening (rate limiting, validation, headers)
- Moved save/load UI to bottom of page
- Fixed input validation for empty/default values
- Removed proposed closing date field
- Updated license file

### v1.0
- Initial release
- Multi-offer comparison
- Cash flow timeline graph
- Seller note with balloon payment support
- Session-based storage
- CSRF protection

## Support & Contributing

For issues, feature requests, or contributions, please contact the project owner.

## License

Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International

Copyright (c) 2025 Offer Comparison Tool

This work is licensed under the Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.

**You are free to:**
- Share — copy and redistribute the material in any medium or format
- Adapt — remix, transform, and build upon the material

**Under the following terms:**
- Attribution — You must give appropriate credit
- NonCommercial — You may not use the material for commercial purposes
- ShareAlike — If you remix, you must distribute under the same license

For commercial licensing inquiries, please contact the project owner.

Full license: https://creativecommons.org/licenses/by-nc-sa/4.0/legalcode

---

© 2025 Rico Vision LLC
