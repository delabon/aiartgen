## AIArtGen

This project is a web application built with PHP and Laravel, designed to manage and showcase art collections. Users can register, log in, and manage their art pieces. The application includes features for user authentication, email verification, password reset, and user-specific art management.

### Key Features

- **User Authentication**: Secure user registration and login functionality.
- **Email Verification**: Users must verify their email addresses before accessing certain features.
- **Password Reset**: Users can reset their passwords via email.
- **Art Management**: Authenticated users can create, edit, and delete their art pieces.
- **User Profiles**: View art collections by specific users.
- **Responsive Design**: Optimized for both desktop and mobile devices.
- **Notifications**: Email notifications for account verification and password reset.
- **Role-Based Access Control**: Permissions to ensure only authorized users can perform certain actions.

## How to setup

### Add domain to /etc/hosts (host)

```bash
sudo nano /etc/hosts
127.0.0.111  aiartgen.test
```

### Install mkcert (host)

```bash
sudo apt install libnss3-tools
curl -JLO "https://dl.filippo.io/mkcert/latest?for=linux/amd64"
chmod +x mkcert-v*-linux-amd64
sudo mv mkcert-v*-linux-amd64 /usr/local/bin/mkcert
cd config/ssls/
mkcert -install aiartgen.test
```

### Up containers (host)

```bash
docker-compose up --build -d
```

### Connect to container bash (host)

```bash
docker exec -it php-container bash
```

### Npm

```bash
docker-compose run --rm node-service npm install
docker-compose run --rm node-service npm run build
```
