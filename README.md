# Team Task Manager

A modern task management system built with Laravel, featuring team collaboration, real-time notifications, and automated task reminders.

## Features

- User authentication with 2FA support
- Team management and collaboration
- Task creation and assignment
- Real-time notifications
- Email notifications and reminders
- Role-based access control
- Activity logging
- API access
- Queue system for background jobs

## Requirements

- PHP >= 8.2
- MySQL >= 8.0
- Composer
- Node.js >= 16
- Redis (for queues and caching)
- Supervisor (for queue workers)

## Installation

1. Clone the repository:
```bash
git clone https://github.com/yourusername/team-task-manager.git
cd team-task-manager
```

2. Install PHP dependencies:
```bash
composer install
```

3. Install and compile frontend assets:
```bash
npm install
npm run build
```

4. Copy the environment file and configure your environment:
```bash
cp .env.example .env
php artisan key:generate
```

5. Configure your `.env` file with your database and mail settings:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=team_task_manager
DB_USERNAME=your_username
DB_PASSWORD=your_password

MAIL_MAILER=smtp
MAIL_HOST=your_mail_host
MAIL_PORT=587
MAIL_USERNAME=your_mail_username
MAIL_PASSWORD=your_mail_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourapp.com
MAIL_FROM_NAME="${APP_NAME}"

QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

6. Run database migrations and seed initial data:
```bash
php artisan migrate --seed
```

## Queue Workers

The application uses queues for processing background jobs like sending emails and notifications. Set up queue workers using Supervisor:

1. Copy the Supervisor configuration:
```bash
sudo cp supervisor.conf /etc/supervisor/conf.d/team-task-manager.conf
```

2. Update the Supervisor configuration:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start team-task-manager-worker:*
```

## Scheduled Tasks

The application uses Laravel's task scheduler for recurring tasks. Add the following Cron entry to your server:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## Testing

Run the test suite:
```bash
php artisan test
```

Run with coverage report:
```bash
php artisan test --coverage
```

## API Documentation

API documentation is available in OpenAPI/Swagger format in `docs/api.yaml`. You can view it using any OpenAPI viewer or serve it using tools like `swagger-ui`.

To serve the API documentation locally:
```bash
npm install -g swagger-ui-cli
swagger-ui-cli serve docs/api.yaml
```

## Deployment

The application includes GitHub Actions workflows for automated testing and deployment. To set up deployment:

1. Configure the following secrets in your GitHub repository:
   - `SSH_PRIVATE_KEY`: Your deployment server's SSH private key
   - `SSH_HOST`: Your server's hostname
   - `SSH_USER`: SSH username
   - `SLACK_WEBHOOK`: Slack webhook URL for deployment notifications

2. Push to the main branch to trigger deployment:
```bash
git push origin main
```

## Security

- All routes are protected by authentication middleware
- CSRF protection is enabled for web routes
- API routes use sanctum tokens
- Password requirements are enforced
- Two-factor authentication is available
- All activities are logged
- Regular security audits are performed via GitHub Actions

## Maintenance

### Queue Monitoring

Monitor queue health:
```bash
php artisan queue:monitor
```

### Log Management

View application logs:
```bash
tail -f storage/logs/laravel.log
```

### Database Backups

Create a backup:
```bash
php artisan backup:run
```

### Cache Management

Clear application cache:
```bash
php artisan optimize:clear
```

Rebuild cache:
```bash
php artisan optimize
```

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support, email support@yourapp.com or create an issue in the GitHub repository.
