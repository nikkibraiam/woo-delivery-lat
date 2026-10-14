<?php 

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * On the thank-you page: attach the chosen branch and delivery location to the
 * order, then notify the branch by email (template post) and browser push.
 */
function woo_del_lat_payment_complete( $order_id ){
    if(isset($_COOKIE["woo_del_lat_office"])){
        $officeId = absint($_COOKIE["woo_del_lat_office"]);
        add_post_meta($order_id, "woo_del_lat_office", $officeId);
        if(isset($_COOKIE["woo_del_lat_address"])){
            $latitude = (float)($_COOKIE["woo_del_lat_locationlat"] ?? 0);
            $longitude = (float)($_COOKIE["woo_del_lat_locationlng"] ?? 0);
            add_post_meta($order_id, "woo_del_lat_locationlat", $latitude);
            add_post_meta($order_id, "woo_del_lat_locationlng", $longitude);

            $officeService = new BranchOfficeService();
            $office = $officeService->get($officeId);
            $officeName = $office ? $office->name : "";

            $templateId = get_option("woo_del_lat_templateEmail");
            
            if($templateId){
                $template = get_post($templateId);
                $content = $template->post_content;
                $content = apply_filters('the_content', $content);
                
                if(strpos($content, "##officeName") !== false){
                    $content = str_replace("##officeName", esc_html($officeName), $content);
                }
                if(strpos($content, "##urlGoogleDestination") !== false){
                    $url = "https://www.google.com/maps/place/$latitude,$longitude";
                    $content = str_replace("##urlGoogleDestination", esc_url($url), $content);
                }
                if(strpos($content, "##addressDestination") !== false){
                    $address = sanitize_text_field(wp_unslash($_COOKIE["woo_del_lat_address"]));
                    $content = str_replace("##addressDestination", esc_html($address), $content);
                }
                if(strpos($content, "##urlOrden") !== false){
                    $urlOrder = get_site_url()."/wp-admin/post.php?post=$order_id&action=edit";
                    $content = str_replace("##urlOrden", $urlOrder, $content);
                }
                $destination = get_option("woo_del_lat_notificationEmails");
                wp_mail($destination, "Ha llegado una nueva orden #$order_id para entrega en domicilio", $content);
                
                setcookie("woo_del_lat_office", "", time()-600, "/");
                setcookie("woo_del_lat_address", "", time()-600, "/");
                setcookie("woo_del_lat_locationlat", "", time()-600, "/");
                setcookie("woo_del_lat_locationlng", "", time()-600, "/");  
            }
            if(get_option("woo_del_lat_activeNotification") == "on"){
                global $wpdb;

                $select = "SELECT DISTINCT $wpdb->usermeta.meta_value FROM $wpdb->usermeta WHERE $wpdb->usermeta.meta_key = 'woo_del_lat_token'";
            
                $tokens = $wpdb->get_results($select);

                if($tokens){
                    foreach($tokens as $token){
                        sendNotification($order_id, $officeName, $token->meta_value);
                    }
                }
            }
        }
    }
}
add_action( 'woocommerce_before_thankyou', 'woo_del_lat_payment_complete', 100, 1 );

function sendNotification($order_id, $officeName, $tokenDestination){
    $message = ["title" => "Nueva Orden #$order_id", "body" => "Ha llegado una nueva orden para la sede $officeName"];

    $data = array(
        'to' => $tokenDestination,
        'notification' => json_encode($message)
    );

    $headers = array(
        'Content-type: application/json',
        'Authorization: key=' . get_option("woo_del_lat_fcmServerKey"),
    );
    // FCM legacy HTTP API (shut down by Google in 2024; see README).
    $url = 'https://fcm.googleapis.com/fcm/send';
    $ch = curl_init($url);
    $postString = http_build_query($data, '', '&');
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
}

function register_token_user(){
    ?>
    <script type="text/javascript" src="<?php echo esc_url(WOO_DEL_LAT_URL . "public/js/woo-delivery-geolocation.js"); ?>"></script>

    <script src="https://www.gstatic.com/firebasejs/8.6.5/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.6.5/firebase-messaging.js"></script>
    <script type="text/javascript">
        
    var firebaseConfig = {
        apiKey: "<?php echo esc_js(get_option("woo_del_lat_firebaseApiKey")); ?>",
        authDomain: "<?php echo esc_js(get_option("woo_del_lat_firebaseAuthDomain")); ?>",
        projectId: "<?php echo esc_js(get_option("woo_del_lat_firebaseProjectId")); ?>",
        storageBucket: "<?php echo esc_js(get_option("woo_del_lat_firebaseStorageBucket")); ?>",
        messagingSenderId: "<?php echo esc_js(get_option("woo_del_lat_firebaseSenderId")); ?>",
        appId: "<?php echo esc_js(get_option("woo_del_lat_firebaseAppId")); ?>"
    };
    // Initialize Firebase
    firebase.initializeApp(firebaseConfig);

    const messaging = firebase.messaging();

    messaging.onMessage((payload) => {
        console.log('Message received. ', payload);
    });

    const pushNotificationSuported = isPushNotificationSupported();
    if (pushNotificationSuported) {
        initializePushNotifications().then(function (consent) {
            if (consent === 'granted') {
                if ('serviceWorker' in navigator) {
                    navigator.serviceWorker.register('<?php echo esc_js(WOO_DEL_LAT_URL . "public/js/sw.js"); ?>').then(function (registration) {
                        console.log('Service worker registration succeeded:', registration);
                        messaging.getToken({
                            serviceWorkerRegistration: registration,
                            vapidKey:
                                '<?php echo esc_js(get_option("woo_del_lat_firebaseVapidKey")); ?>'
                        }).then((currentToken) => {
                            if (currentToken) {
                                var form_data = new FormData();
                                form_data.append('token', currentToken);
                                form_data.append('action', 'woo_delivery_lat_add_token');
                                    jQuery.ajax({
                                        url: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',
                                        type: 'post',
                                        contentType: false,
                                        processData: false,
                                        data: form_data,
                                        success: function (response) {},
                                        error: function (response) {
                                            console.log("error:");
                                            console.log(response);
                                        }
                                    });
                                
                            } else {
                                console.log('No registration token available. Request permission to generate one.');
                            }
                        });
                    });
                } else {
                    console.log('Service workers are not supported.');
                }
            }
        });
    }

    /**
     * checks if Push notification and service workers are supported by your browser
     */
    function isPushNotificationSupported() {
        return "serviceWorker" in navigator && "PushManager" in window;
    }

    /**
     * asks user consent to receive push notifications and returns the response of the user, one of granted, default, denied
     */
    function initializePushNotifications() {
        // request user grant to show notification
        return Notification.requestPermission(function(result) {
            return result;
        });
    }

    </script>
<?php }
//add_action('admin_init', 'register_token_user', 100000);