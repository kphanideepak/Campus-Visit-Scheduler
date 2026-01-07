# Campus Visit Scheduler

A comprehensive WordPress plugin for managing school tour bookings. Allows parents to book campus visits online while giving administrators full control over tour schedules, capacity, and notifications.

## Features

### For Parents
- Easy-to-use booking form with date and time selection
- Real-time availability display
- Instant email confirmation
- Add tour to calendar (ICS download)
- Mobile-responsive design

### For Administrators
- Configure recurring weekly tours or one-off special events
- Set capacity limits per time slot
- Manage blackout dates (holidays, closures)
- View and manage all bookings
- Calendar view for quick overview
- Export bookings to CSV
- Customizable email templates
- Multiple notification recipients
- Detailed reporting and statistics

## Installation

1. Upload the `campus-visit-scheduler` folder to `/wp-content/plugins/`
2. Activate the plugin through WordPress admin
3. Go to Campus Visits > Settings to configure
4. Add the shortcode `[campus_visit_scheduler]` to any page

## Shortcodes

- `[campus_visit_scheduler]` - Display the booking form
- `[campus_visit_calendar]` - Display a tour availability calendar

## Requirements

- WordPress 5.8+
- PHP 7.4+

## Configuration

1. **Tour Schedule**: Add recurring or one-off tour time slots
2. **Capacity**: Set maximum groups per slot and group size limits
3. **Blackout Dates**: Block dates when tours are unavailable
4. **Notifications**: Configure admin email recipients
5. **Email Templates**: Customize confirmation and reminder emails

## License

GPL v2 or later
