# Attendance Management System

A comprehensive web-based attendance management system built with PHP, MySQL, and Bootstrap. This system allows administrators to manage employees and track attendance, while employees can clock in/out and view their attendance history.

## Features

### Admin Features
- **Dashboard**: Overview of attendance statistics and recent activity
- **Employee Management**: Add, edit, and delete employee records
- **Reports**: View filtered attendance reports with statistics
- **Export**: Export attendance data to Excel (CSV) and PDF formats
- **Real-time Statistics**: Track present, late, and absent employees

### Employee Features
- **Time Clock**: Clock in/out with real-time display
- **Attendance History**: View recent attendance records
- **Monthly Statistics**: Track personal attendance metrics
- **Responsive Design**: Works on desktop and mobile devices

## System Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Modern web browser

## Installation

### 1. Database Setup

1. Create a MySQL database named `attendance_system`
2. Import the database structure (tables will be created automatically on first run)

### 2. Configuration

1. Open `config/db.php`
2. Update the database connection settings:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   define('DB_NAME', 'attendance_system');
   ```

### 3. Web Server Setup

1. Place the project files in your web server directory
2. Ensure the web server has read/write permissions
3. Access the system via your web browser

### 4. Default Login

- **Username**: admin
- **Password**: admin123

**Important**: Change the default password after first login!

## Directory Structure

```
attendance-system/
│
├── config/
│   └── db.php                # Database connection
│
├── auth/
│   ├── login.php             # Login page
│   ├── logout.php            # Logout script
│
├── admin/
│   ├── dashboard.php         # Admin dashboard
│   ├── employees.php         # Add/Edit/Delete employees
│   ├── reports.php           # View reports
│   └── export.php            # Export to Excel/PDF
│
├── employee/
│   ├── dashboard.php         # Employee dashboard (time in/out)
│
├── assets/
│   ├── css/                  # Stylesheets
│   └── js/                   # JavaScript files
│
├── index.php                 # Landing page
├── init.php                  # Session initialization
└── README.md                 # This file
```

## Database Schema

### Users Table
- `id` - Primary key
- `username` - Unique username
- `password` - Hashed password
- `email` - User email
- `full_name` - User's full name
- `role` - 'admin' or 'employee'
- `created_at` - Timestamp

### Employees Table
- `id` - Primary key
- `employee_id` - Unique employee ID
- `full_name` - Employee's full name
- `email` - Employee email
- `department` - Department name
- `position` - Job position
- `hire_date` - Date hired
- `status` - 'active' or 'inactive'
- `created_at` - Timestamp

### Attendance Table
- `id` - Primary key
- `employee_id` - Foreign key to employees
- `date` - Attendance date
- `time_in` - Clock in time
- `time_out` - Clock out time
- `total_hours` - Hours worked
- `status` - 'present', 'late', 'absent', 'half-day'
- `created_at` - Timestamp

## Usage

### For Administrators

1. **Login** with admin credentials
2. **Add Employees** through the Employees section
3. **View Reports** to monitor attendance
4. **Export Data** for analysis or reporting

### For Employees

1. **Login** with employee credentials
2. **Clock In/Out** using the time clock
3. **View History** of attendance records
4. **Check Statistics** for monthly overview

## Security Features

- Password hashing using PHP's `password_hash()`
- Session-based authentication
- SQL injection prevention with prepared statements
- XSS protection with `htmlspecialchars()`
- Role-based access control

## Customization

### Adding New Features

1. Create new PHP files in appropriate directories
2. Update navigation menus
3. Add database tables if needed
4. Test thoroughly

### Styling

- Modify `assets/css/style.css` for custom styling
- Bootstrap 5 classes are used for responsive design
- Font Awesome icons for visual elements

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check database credentials in `config/db.php`
   - Ensure MySQL service is running

2. **Permission Errors**
   - Set proper file permissions (755 for directories, 644 for files)
   - Ensure web server can read/write to the directory

3. **Session Issues**
   - Check PHP session configuration
   - Ensure cookies are enabled

4. **Export Not Working**
   - Check PHP memory limits
   - Ensure proper headers are set

## Support

For issues or questions:
1. Check the troubleshooting section above
2. Review PHP error logs
3. Ensure all requirements are met
4. Test with default credentials

## License

This project is open source and available under the MIT License.

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

---

**Note**: This system is designed for educational and small business use. For production environments, consider additional security measures and regular backups. 