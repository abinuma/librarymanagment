-- Migration: Create Settings Table
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value VARCHAR(255) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Insert default settings if they don't exist
INSERT IGNORE INTO settings (setting_key, setting_value, description) VALUES
('borrow_duration_days', '14', 'Default borrow period in days'),
('fine_per_day', '1.00', 'Fine amount per overdue day'),
('max_borrow_limit', '5', 'Max books a member can borrow at once');
