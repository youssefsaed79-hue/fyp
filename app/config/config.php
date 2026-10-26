<?php
declare(strict_types=1);

/**
 * InternFlow - Main Configuration File
 * Final Year Project (FYP) - UNITEN CCI
 */

return [
    'app' => [
        'name' => 'InternFlow',
        'institution' => 'UNITEN College of Computing and Informatics (CCI)',
        'env' => 'development', // 'development' or 'production'
        'debug' => true,
        // Base URL relative or absolute. Auto-detected if empty.
        'base_url' => '',
        'timezone' => 'Asia/Kuala_Lumpur',
    ],

    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'dbname' => 'internflow',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    ],

    'session' => [
        'name' => 'INTERNFLOW_SESS',
        'lifetime' => 1800, // 30 minutes inactivity timeout (NFR-SEC-02)
        'cookie_secure' => false, // Set to true when running on HTTPS
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
    ],

    'mail' => [
        // If smtp is false, all emails are logged to the `email_log` table & storage/mail.log
        'use_smtp' => false,
        'host' => 'smtp.mailtrap.io',
        'port' => 2525,
        'username' => '',
        'password' => '',
        'encryption' => 'tls',
        'from_email' => 'no-reply@uniten.edu.my',
        'from_name' => 'InternFlow - UNITEN CCI Internship',
        'log_path' => dirname(__DIR__, 2) . '/storage/mail.log',
    ],

    'uploads' => [
        'max_size_mb' => 10, // 10 MB maximum (NFR-PERF-02)
        'max_size_bytes' => 10 * 1024 * 1024,
        'storage_path' => dirname(__DIR__, 2) . '/storage/uploads',
        'allowed_types' => [
            'pdf'  => ['application/pdf'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'],
            'doc'  => ['application/msword'],
            'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/zip'],
            'ppt'  => ['application/vnd.ms-powerpoint'],
            'png'  => ['image/png'],
            'jpg'  => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'webp' => ['image/webp'],
        ],
    ],

    'security' => [
        'token_expiry_hours' => 72, // Invitation token lifespan
        'password_reset_expiry_hours' => 2,
    ],
];
