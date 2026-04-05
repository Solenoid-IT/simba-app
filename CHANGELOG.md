# Changelog

All changes of code will be reported here.

## [1.1.1] - 2026-04-05

### Fixed
- Client detection for Alert service (WebSocket Server) on Vite proxy

## [1.1.0] - 2026-04-05

### Added
- Command `log:watch` on x for monitoring logs (error or output) of a specific service (like apache, mysql, redis, alert-micro)
- Events: Standard way to send an event from the backend app to frontend clients on realtime through Alert service (WebSocket Server)
- Facility `mysql_datetime` to get current time on mysql format

### Improved
- Command `cert:generate` on x with force option (avoid recreating self-signed cert if not expired yet)

## [1.0.0] - 2025-03-20
- Initial release