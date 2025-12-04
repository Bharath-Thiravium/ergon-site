-- Insert shipping addresses for production
INSERT INTO `finance_customershippingaddress` (`customer_id`, `label`, `address_line1`, `city`, `state`, `country`, `is_default`, `created_at`, `updated_at`) VALUES
(9, 'Torrent Urja 14 Pvt Ltd', 'Industrial Area', 'Ahmedabad', 'Gujarat', 'India', 1, NOW(), NOW()),
(23, 'Pantech Synergy Private Limited', 'Tech Park', 'Bangalore', 'Karnataka', 'India', 1, NOW(), NOW()),
(23, 'Parvathi Dyeing Private Limited', 'Textile Hub', 'Tirupur', 'Tamil Nadu', 'India', 0, NOW(), NOW()),
(23, 'Pro-Zeal Green Power Five Pvt. Ltd.', 'Green Energy Complex', 'Chennai', 'Tamil Nadu', 'India', 0, NOW(), NOW()),
(10, 'Prathama Solarconnect Energy Pvt Ltd', 'Veppankullam Village, Sivagangai District', 'Sivagangai', 'Tamil Nadu', 'India', 1, NOW(), NOW()),
(28, 'Colortone Textiles Pvt Ltd.', 'Textile Industrial Estate', 'Coimbatore', 'Tamil Nadu', 'India', 1, NOW(), NOW());