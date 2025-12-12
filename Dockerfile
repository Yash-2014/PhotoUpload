# Use official PHP image with Apache
FROM php:8.2-apache

# Copy the source code into the container
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html/

# Expose port 80
EXPOSE 80
