<?php
if (!defined('ABSPATH')) {
    exit;
}

class CODVerifierAjax {
    
    public function __construct() {
        add_action('wp_ajax_cod_send_otp', array($this, 'send_otp'));
        add_action('wp_ajax_nopriv_cod_send_otp', array($this, 'send_otp'));
        add_action('wp_ajax_cod_verify_otp', array($this, 'verify_otp'));
        add_action('wp_ajax_nopriv_cod_verify_otp', array($this, 'verify_otp'));
        add_action('wp_ajax_cod_token_payment', array($this, 'handle_token_payment'));
        add_action('wp_ajax_nopriv_cod_token_payment', array($this, 'handle_token_payment'));
    }
    
    public function send_otp() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'cod_verifier_nonce')) {
            wp_send_json_error(__('Security check failed.', 'cod-verifier'));
            return;
        }
        
        $phone = sanitize_text_field($_POST['phone']);
        
        if (empty($phone) || !preg_match('/^[6-9]\d{9}$/', $phone)) {
            wp_send_json_error(__('Please enter a valid 10-digit mobile number.', 'cod-verifier'));
            return;
        }
        
        $test_mode = get_option('cod_verifier_test_mode', '1');
        
        if (!session_id()) {
            session_start();
        }
        
        // Generate OTP
        $otp = sprintf('%06d', rand(100000, 999999));
        $_SESSION['cod_otp'] = $otp;
        $_SESSION['cod_otp_phone'] = $phone;
        $_SESSION['cod_otp_time'] = time();
        $_SESSION['cod_otp_verified'] = false;
        
        if ($test_mode === '1') {
            // Test mode - return OTP in response
            wp_send_json_success(array(
                'message' => __('OTP sent successfully! (Test Mode)', 'cod-verifier'),
                'otp' => $otp,
                'test_mode' => true
            ));
        } else {
            // Production mode - send actual SMS via Twilio
            $result = $this->send_twilio_sms($phone, $otp);
            
            if ($result['success']) {
                wp_send_json_success(array(
                    'message' => __('OTP sent successfully to your mobile number!', 'cod-verifier')
                ));
            } else {
                wp_send_json_error($result['message']);
            }
        }
    }
    
    private function send_twilio_sms($phone, $otp) {
        try {
            // Get Twilio settings
            $sid = get_option('cod_verifier_twilio_sid', '');
            $token = get_option('cod_verifier_twilio_token', '');
            $twilio_number = get_option('cod_verifier_twilio_number', '');
            
            if (empty($sid) || empty($token) || empty($twilio_number)) {
                return array(
                    'success' => false,
                    'message' => __('Twilio SMS service not configured. Please contact administrator.', 'cod-verifier')
                );
            }
            
            // Load Twilio SDK
            $twilio_autoload = COD_VERIFIER_PLUGIN_PATH . 'includes/twilio-sdk/src/Twilio/autoload.php';
            
            if (!file_exists($twilio_autoload)) {
                error_log('COD Verifier: Twilio SDK not found at ' . $twilio_autoload);
                return array(
                    'success' => false,
                    'message' => __('SMS service temporarily unavailable. Please try again later.', 'cod-verifier')
                );
            }
            
            require_once $twilio_autoload;
            
            // Format phone number for international format
            $formatted_phone = '+91' . $phone; // Assuming Indian numbers
            
            // Create Twilio client
            $client = new \Twilio\Rest\Client($sid, $token);
            
            // Send SMS
            $message = "Your COD verification OTP is: {$otp}. Valid for 5 minutes. Do not share this code.";
            
            $result = $client->messages->create(
                $formatted_phone,
                array(
                    'from' => $twilio_number,
                    'body' => $message
                )
            );
            
            if ($result->sid) {
                error_log('COD Verifier: SMS sent successfully. SID: ' . $result->sid);
                return array(
                    'success' => true,
                    'message' => __('OTP sent successfully!', 'cod-verifier')
                );
            } else {
                error_log('COD Verifier: SMS sending failed - no SID returned');
                return array(
                    'success' => false,
                    'message' => __('Failed to send OTP. Please try again.', 'cod-verifier')
                );
            }
            
        } catch (\Twilio\Exceptions\RestException $e) {
            error_log('COD Verifier: Twilio REST Exception: ' . $e->getMessage());
            return array(
                'success' => false,
                'message' => __('SMS service error. Please check your phone number and try again.', 'cod-verifier')
            );
        } catch (Exception $e) {
            error_log('COD Verifier: General Exception: ' . $e->getMessage());
            return array(
                'success' => false,
                'message' => __('Failed to send OTP. Please try again later.', 'cod-verifier')
            );
        }
    }
    
    public function verify_otp() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'cod_verifier_nonce')) {
            wp_send_json_error(__('Security check failed.', 'cod-verifier'));
            return;
        }
        
        $otp = sanitize_text_field($_POST['otp']);
        
        if (!session_id()) {
            session_start();
        }
        
        $stored_otp = isset($_SESSION['cod_otp']) ? $_SESSION['cod_otp'] : '';
        $otp_time = isset($_SESSION['cod_otp_time']) ? $_SESSION['cod_otp_time'] : 0;
        
        if (empty($stored_otp)) {
            wp_send_json_error(__('No OTP found. Please request a new OTP.', 'cod-verifier'));
            return;
        }
        
        // Check if OTP is expired (5 minutes)
        if (time() - $otp_time > 300) {
            unset($_SESSION['cod_otp']);
            wp_send_json_error(__('OTP expired. Please request a new OTP.', 'cod-verifier'));
            return;
        }
        
        if ($otp === $stored_otp) {
            $_SESSION['cod_otp_verified'] = true;
            wp_send_json_success(__('OTP verified successfully!', 'cod-verifier'));
        } else {
            wp_send_json_error(__('Invalid OTP. Please try again.', 'cod-verifier'));
        }
    }
    
    public function handle_token_payment() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'cod_verifier_nonce')) {
            wp_send_json_error(__('Security check failed.', 'cod-verifier'));
            return;
        }
        
        $test_mode = get_option('cod_verifier_test_mode', '1');
        
        if (!session_id()) {
            session_start();
        }
        
        if ($test_mode === '1') {
            // Test mode - simulate payment
            $_SESSION['cod_token_paid'] = true;
            wp_send_json_success(__('Token payment completed successfully! (Test Mode)', 'cod-verifier'));
        } else {
            // Production mode - integrate with Razorpay
            $razorpay_key_id = get_option('cod_verifier_razorpay_key_id', '');
            $razorpay_key_secret = get_option('cod_verifier_razorpay_key_secret', '');
            
            if (empty($razorpay_key_id) || empty($razorpay_key_secret)) {
                wp_send_json_error(__('Payment gateway not configured. Please contact administrator.', 'cod-verifier'));
                return;
            }
            
            // Create Razorpay order
            $order_data = array(
                'amount' => 100, // ₹1 in paise
                'currency' => 'INR',
                'receipt' => 'cod_token_' . time(),
                'notes' => array(
                    'purpose' => 'COD Token Payment'
                )
            );
            
            $response = wp_remote_post('https://api.razorpay.com/v1/orders', array(
                'headers' => array(
                    'Authorization' => 'Basic ' . base64_encode($razorpay_key_id . ':' . $razorpay_key_secret),
                    'Content-Type' => 'application/json'
                ),
                'body' => json_encode($order_data),
                'timeout' => 30
            ));
            
            if (is_wp_error($response)) {
                wp_send_json_error(__('Failed to create payment order. Please try again.', 'cod-verifier'));
                return;
            }
            
            $body = wp_remote_retrieve_body($response);
            $result = json_decode($body, true);
            
            if (isset($result['id'])) {
                // For production, we would return Razorpay order details
                // For now, simulate successful payment
                $_SESSION['cod_token_paid'] = true;
                wp_send_json_success(__('Token payment completed successfully!', 'cod-verifier'));
            } else {
                wp_send_json_error(__('Failed to process payment. Please try again.', 'cod-verifier'));
            }
        }
    }
}

new CODVerifierAjax();
?>