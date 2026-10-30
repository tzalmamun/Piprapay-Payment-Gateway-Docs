CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    pp_id VARCHAR(50) NULL,
    purpose VARCHAR(50) NOT NULL DEFAULT 'course', -- 'course' বা 'wallet' বা 'product'
    course_id INT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(10) NOT NULL DEFAULT 'BDT',
    status VARCHAR(30) NOT NULL DEFAULT 'pending', -- 'pending', 'completed', 'failed'
    payment_method VARCHAR(50) NULL,
    trx_id VARCHAR(100) NULL,
    sender_number VARCHAR(30) NULL,
    raw_response TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL,
    INDEX(user_id),
    INDEX(pp_id),
    INDEX(status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
