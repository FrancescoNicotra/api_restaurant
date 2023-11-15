# Introduction

It's a Laravel application where two Docker containers are used for MySQL and phpMyAdmin to manage the database.

# Installation

1. Clone the repository
2. Run `docker-compose up -d`

# Usage

1. Open http://localhost:8080 in your browser
2. Try with postman or any other tool to send a POST request with a JSON body like this:

```
{
    "url": "https://www.google.com"
}
```
