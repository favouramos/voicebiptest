<?php
return array (
  'app' => 
  array (
    'name' => 'Sophia The Promoter — Appointment Booking',
    'base_url' => 'https://calling.sophiathepromoter.com',
    'timezone' => 'Africa/Lagos',
    'session_name' => 'sophia_booking_admin',
  ),
  'admin' => 
  array (
    'username' => 'admin',
    'password_hash' => '$2y$12$lpbgRRNIX9/6ZqlrMOCNIOH21Xoy8wen/QwaRTaMla7wYhHDuWNSC',
  ),
  'db' => 
  array (
    'driver' => 'mysql',
    'host' => 'localhost',
    'port' => 3306,
    'name' => 'coachste_callingai',
    'user' => 'coachste_callingai',
    'pass' => 'Zaddy2024@',
    'charset' => 'utf8mb4',
  ),
  'voicebip' => 
  array (
    'api_base' => 'https://api.voicebip.com/v1',
    'api_key' => '#',
    'agent_id' => 'agt_OMVX_h4jypdm6062c',
    'signing_secret' => '#',
    'webhook_url' => 'https://calling.sophiathepromoter.com/api/voicebip.php',
  ),
  'booking' => 
  array (
    'default_status' => 'confirmed',
    'business_name' => 'Sophia The Promoter',
  ),
);
