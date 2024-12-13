# e-Commerce API Gateway

This project is an API Gateway for an e-commerce platform. It routes requests to various microservices and handles authentication, rate limiting, caching, and more.

## Project Structure

### Root Directory

- **index.php**: The main entry point for the API Gateway. It routes incoming requests to the appropriate controllers based on the defined routes.

### Directories

- **middlewares/**: Contains middleware functions for handling various aspects of the request lifecycle.
  - **auth.php**: Handles authentication and token validation.
  - **cache.php**: Manages caching of responses.
  - **cors.php**: Handles Cross-Origin Resource Sharing (CORS) settings.
  - **rate_limit.php**: Implements rate limiting to prevent abuse.
  - **sanitize.php**: Sanitizes input data to prevent security vulnerabilities.

- **utils/**: Contains utility functions and classes.
  - **db.php**: Defines the `Database` class for managing database connections.
  - **logger.php**: Contains functions for logging requests and responses.

- **logs/**: Directory where log files are stored.
  - **gateway.log**: Log file for recording request and response details.

- **vendor/**: Directory for Composer dependencies.

### Key Files

- **routes.php**: Defines the routes for the API Gateway, mapping URL patterns to controller actions.

### Usage

1. **Start the Server**: Run the following command to start the PHP built-in server:
 ```sh
 php -S localhost:8080 -t ./api-gateway
 ```

2 Access the API: Use tools like Postman or curl to send requests to the API Gateway at http://localhost:8080.

**Example Endpoints**
-GET /api/products: Retrieve a list of products.
-POST /api/auth/signup: Register a new user.
-POST /api/auth/signin: Authenticate a user.

**Logging**
All requests and responses are logged in the logs/gateway.log file for debugging and monitoring purposes.

**Middleware**

- Authentication: Ensures that requests are authenticated using JWT tokens. (by Marjorie Polistico)
- Caching: Caches responses to improve performance (by Almarie Maestrado).
- CORS: Manages cross-origin requests (by Michelle Malto).
- Logging: The logger.php file in utils contains functions for logging request and response details to logs/gateway.log. (by Rhea Bete)
- Rate Limiting: Limits the number of requests to prevent abuse. (by Francis Tin-ao)
- Sanitization: Sanitizes input data to prevent security vulnerabilities. (by Francis Tin-ao)
- Database: The Database class in utils/db.php manages the connection to the MySQL database. It uses PDO for database interactions. (by Francis Tin-ao)
- Server and Route Initialization: Server was setup and all of the components are tied together in a single gateway with route mapping. (by Francis Tin-ao)

### License

This project is licensed under the MIT License.