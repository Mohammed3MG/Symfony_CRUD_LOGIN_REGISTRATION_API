# Symfony_CRUD_LOGIN_REGISTRATION_API
### Symfony REST API Application with User Registration, JWT Authentication, and CRUD Operations

This project is a Symfony-based web application that demonstrates a fully functional REST API, enabling user registration and authentication, secured with JSON Web Tokens (JWT). It also supports complete CRUD operations (Create, Read, Update, Delete) with seamless MySQL integration. Below, we outline the key features and functionalities of the application:

1. **User Registration**:
    - New users can sign up through the API by providing their basic details, such as username, email, and password.
    - The application ensures that user credentials are securely stored in the MySQL database using hashing mechanisms.
    - Registration endpoints validate input data and prevent duplicate entries, ensuring each user has a unique username and email address.

2. **JWT-Based User Authentication**:
    - The application uses JWT for authentication, ensuring secure and stateless communication between the client and server.
    - After successful registration or login, a JWT is generated and provided to the user. This token must be included in the header of all subsequent requests to access protected resources.
    - JWT tokens are signed and can be verified by the server to ensure authenticity. They contain user-specific data, such as user ID and roles, and have an expiration time to enhance security.

3. **User Login**:
    - Registered users can log in by providing their credentials (Email and Password).
    - Upon successful authentication, the API generates a JWT for the user, which can be used for future API requests.
    - Login endpoints are designed to respond with appropriate error messages in cases of invalid credentials or inactive accounts.

4. **CRUD Operations**:
    - The application supports full CRUD operations for managing resources, such as user profiles, posts, or any other entities defined in the application.
    - **Create**: Users can create new entries in the database. The API ensures that the input data is validated and conforms to the database schema.
    - **Read**: Users can retrieve information from the database. The application provides endpoints to fetch individual records or lists of records, with support for pagination and filtering.
    - **Update**: Users can modify existing records. The API checks user permissions to ensure that updates are authorized and valid.
    - **Delete**: Users can remove records from the database. The application prevents accidental data loss by confirming deletion actions and checking user permissions.

5. **MySQL Integration**:
    - The application is connected to a MySQL database, where all user information and other resources are stored.
    - The database schema is managed using Symfony’s Doctrine ORM, which allows for easy manipulation and interaction with the database through entities and repositories.
    - Migrations are used to maintain the database schema, ensuring that changes to the data structure are tracked and applied consistently.

6. **Security and Validation**:
    - The API is secured using Symfony’s built-in security features, combined with JWT to protect against unauthorized access.
    - Input validation is performed on all API endpoints to prevent invalid data from being stored or processed.
    - CORS (Cross-Origin Resource Sharing) policies are configured to control which domains can interact with the API, enhancing security for frontend applications hosted on different servers.

7. **Error Handling and Responses**:
    - The application provides meaningful error messages for various scenarios, such as invalid input, unauthorized access, and resource not found.
    - The API follows RESTful principles and returns appropriate HTTP status codes and messages for all requests.

8. **Scalability and Extensibility**:
    - The project structure is modular and follows best practices, making it easy to extend with new features or scale with increased traffic.
    - Additional security features, such as refresh tokens, role-based access control, and rate limiting, can be integrated as needed.

### Getting Started

To get started with this project, you need to clone the repository, set up the MySQL database, and configure the environment variables. Detailed setup instructions are provided below:

1. **Clone the Repository**:
   ```
   git clone https://github.com/Mohammed3MG/Symfony_CRUD_LOGIN_REGISTRATION_API.git
   cd Symfony_CRUD_LOGIN_REGISTRATION_API
   ```

2. **Set Up Environment Variables**:
   - Copy `.env.example` to `.env` and fill in your database credentials and JWT secret key.

3. **Install Dependencies**:
   ```
   composer install
   ```

4. **Set Up the Database**:
   ```
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```

5. **Run the Server**:
   ```
   symfony server:start
   ```

6. **Test the API**:
   - You can use tools like Postman or curl to interact with the API endpoints. Start by registering a new user and then logging in to obtain a JWT token.
