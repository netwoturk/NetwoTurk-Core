# Decision 0001: NetwoTürk Core Foundation Rules

## Decision

NetwoTürk Core will remain lightweight, Laravel-standard, shared-hosting-compatible and VPS-migration-ready.

## Reason

The system must work on current cPanel/cloud hosting without requiring Redis, Supervisor, WebSocket or heavy background workers.

## Rules

- Laravel official structure will not be broken.
- public/ will remain the document root.
- Secrets will stay in .env.
- Database changes will use migrations.
- GitHub will be the main source of truth.
- Server updates will use git pull, composer install and php artisan migrate.
- V1 scope will stay limited.

## V1 Scope

- Admin panel
- Customers / Organizations
- Platforms
- Products / Modules
- Domains
- Licenses
- License Check API
- Announcements
- Promotions
- Recommended Modules
- Logs
- Settings

## Out of Scope for V1

- Payment
- Marketplace
- ResellerClub automation
- AI credit system
- Customer panel
- Redis
- WebSocket
- Heavy queue system
