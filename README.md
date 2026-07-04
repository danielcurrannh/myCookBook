# myCookBook

A PHP/MySQL recipe sharing site for Apache.

## Setup

1. Run `db/create_database.sql` as a MySQL admin user.
2. Run `db/create_user.sql` as a MySQL admin user, then change the password in both that script and `config.ini`.
3. Run `db/tables.sql` to create the application tables.
4. Put your PNG logo at `assets/logo.png`. The header falls back to `assets/placeholder-logo.svg` until then.
5. Serve the project directory with Apache and PHP. Ensure `uploads/` is writable by the web server.

