<?php
/*
Plugin Name: Warranty card 
Description: This is a custom WordPress plugin [update_warranty_form].
Version: 1.0
Author: Muthaiah K
*/
// Register activation and deactivation hooks
register_activation_hook(__FILE__, 'my_custom_plugin_install');
register_deactivation_hook(__FILE__, 'my_custom_plugin_uninstall');

function enqueue_datatables_scripts()
{
    // Enqueue jQuery (if not already loaded)
    wp_enqueue_script('jquery');

    // Enqueue DataTables script
    wp_enqueue_script('datatables', 'https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js', array('jquery'), '1.11.5', true);
    wp_enqueue_script('datatables', 'https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js', array('jquery'), '1.11.5', true);
    wp_enqueue_style('custom-styles', plugins_url('/css/custom-styles.css', __FILE__));
    // Enqueue DataTables CSS
    wp_enqueue_style('datatables-css', 'https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css');
}
add_action('admin_enqueue_scripts', 'enqueue_datatables_scripts');
function load_jquery_from_cdn()
{
    // Enqueue jQuery from Google CDN
    wp_enqueue_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js', array(), '3.6.4', true);
}

add_action('wp_enqueue_scripts', 'load_jquery_from_cdn');
/////////////////////////////////////////////////////////////////////Admin Ajax cAll///////////////////////////////////////////////////////////////////
add_action('wp_head', 'myplugin_Cityajaxurl');
function myplugin_Cityajaxurl()
{
    echo '<script type="text/javascript">
           var Cityajaxurl = "' . admin_url('admin-ajax.php') . '";
         </script>';
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Function to create the database table on activation
function my_custom_plugin_install()
{
    global $wpdb;

    $warranty_table_name = $wpdb->prefix . 'Warranty_card';
    $customer_table_name = $wpdb->prefix . 'Customer';
    $raiseComplaint_table_name = $wpdb->prefix . 'raiseComplaint';

    $charset_collate = $wpdb->get_charset_collate();

    // Create the Warranty Card table
    $warranty_sql = "CREATE TABLE $warranty_table_name (
        id INT AUTO_INCREMENT PRIMARY KEY,
        Brand VARCHAR(255) NOT NULL,
        Model_Name VARCHAR(20),
        Model_Number VARCHAR(255) NOT NULL,
        serialnumber VARCHAR(50),
        Batch VARCHAR(255),
        customer_id VARCHAR(255),
        customer_status VARCHAR(20)
    ) $charset_collate;";

    // Create the Customer table
    $customer_sql = "CREATE TABLE $customer_table_name (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        phonenumber VARCHAR(15) NOT NULL,
        email VARCHAR(255) NOT NULL,
        serialnumber VARCHAR(50) NOT NULL,
        customer_ID VARCHAR(255),
        dateofsale DATE,
        dateofRgister DATE,
        location VARCHAR(255),
        dealer VARCHAR(255)
    ) $charset_collate;";

    // Create the Raise Complaint table
    $raiseComplaint_sql = "CREATE TABLE $raiseComplaint_table_name (
        id INT AUTO_INCREMENT PRIMARY KEY,
        complaintname VARCHAR(255) NOT NULL,
        complaintphonenumber VARCHAR(15) NOT NULL,
        complaintemail VARCHAR(255) NOT NULL,
        complaintserialnumber VARCHAR(50) NOT NULL,
        complaintcustomer_ID VARCHAR(255),
        complaintdateofRgister DATE,
        complaintdateofsale DATE,
        complaintdealer VARCHAR(255),
        complaintlocation VARCHAR(255),
        complaintList VARCHAR(255),
        complaintText VARCHAR(255),
        complaintTicketsNumber VARCHAR(255),
        complaintStatus VARCHAR(255)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($warranty_sql);
    dbDelta($customer_sql);
    dbDelta($raiseComplaint_sql); // Use $raiseComplaint_sql here, not $raiseComplaint_table_name
}

// Function to remove the database table on deactivation
function my_custom_plugin_uninstall()
{
    global $wpdb;
    $warranty_table_name = $wpdb->prefix . 'Warranty_card';
    $customer_table_name = $wpdb->prefix . 'Customer';
    $raiseComplaint_table_name = $wpdb->prefix . 'raiseComplaint';
    $wpdb->query("DROP TABLE IF EXISTS $warranty_table_name");
    $wpdb->query("DROP TABLE IF EXISTS $customer_table_name");
    $wpdb->query("DROP TABLE IF EXISTS $raiseComplaint_table_name");
}
// Add a menu item for CSV upload in the WordPress admin menu
add_action('admin_menu', 'my_custom_plugin_menu');

function my_custom_plugin_menu()
{
    add_menu_page('Warranty Card', 'Warranty Card', 'manage_options', 'csv-upload', 'csv_upload_page');
}

// Create the CSV upload page
function csv_upload_page()
{
    if (isset($_POST['upload_csv'])) {
        // Handle the CSV file upload
        handle_csv_upload();
    }

    // Display the upload form
    ?>
    <div class="wrap">
        <div class="upload">Load Serial Number CSV</div>
        <div class="hr"></div>
        <div class="">
            <form class="UploadForm" method="post" enctype="multipart/form-data">
                <input type="file" name="csv_file" accept=".csv" required><br>
                <input class="uplod_btn" type="submit" name="upload_csv" value="Upload CSV">
            </form>
        </div>
    </div>
    <?php
}

// Function to handle CSV file upload and data insertion
function handle_csv_upload()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'Warranty_card';

    if ($_FILES['csv_file']['error'] == 0) {
        $csv_file = $_FILES['csv_file']['tmp_name'];

        if (($handle = fopen($csv_file, 'r')) !== false) {
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                // Assuming your CSV columns match the table columns in the same order
                $Brand = $data[0];
                $Model_Name = $data[1];
                $Model_Number = $data[2];
                $serialnumber = $data[3];
                $Batch = $data[4];

                $wpdb->insert(
                    $table_name,
                    array(
                        'Brand' => $Brand,
                        'Model_Name' => $Model_Name,
                        'Model_Number' => $Model_Number,
                        'serialnumber' => $serialnumber,
                        'Batch' => $Batch,
                        'customer_status' => 0,

                    )
                );
            }
            fclose($handle);
            echo '<div class="updated"><p>Data inserted successfully!</p></div>';
        } else {
            echo '<div class="error"><p>Error opening CSV file</p></div>';
        }
    }
}

add_action('admin_menu', 'my_custom_submenu_page');
function my_custom_submenu_page()
{
    // First Submenu Page
    add_submenu_page(
        'csv-upload',
        'Serial Number',
        'Serial Number',
        'manage_options',
        'warranty-cards',
        'display_warranty_cards'
    );

    // Second Submenu Page
    add_submenu_page(
        'csv-upload',
        'Complaints',
        // Page title for the second submenu
        'Complaints',
        // Menu title for the second submenu
        'manage_options',
        'complaints',
        // Menu slug for the second submenu
        'display_complaints' // Callback function to display the table for the second submenu
    );
    add_submenu_page(
        'csv-upload',
        'Reports',
        // Page title for the second submenu
        'Reports',
        // Menu title for the second submenu
        'manage_options',
        'Reports',
        // Menu slug for the second submenu
        'display_Reports' // Callback function to display the table for the second submenu
    );
}

function display_Reports()
{

}

function display_complaints()
{
    global $wpdb;

    // Table name
    $raiseComplaint_table_name = $wpdb->prefix . 'raisecomplaint';

    // Check if a status change request has been made
    if (isset($_POST['complaint_id'])) {
        $complaint_id = intval($_POST['complaint_id']);
        $new_status = intval($_POST['new_status']);

        // Update the complaint status in the database
        $updated = $wpdb->update(
            $raiseComplaint_table_name,
            array('complaintStatus' => $new_status),
            array('id' => $complaint_id)
        );

        if ($updated !== false) {
            // The update was successful
            echo "Complaint status updated successfully.";
        } else {
            // There was an error with the update
            echo "Error updating complaint status: " . $wpdb->last_error;
        }
    }


    // Retrieve complaints data from the database
    $complaints = $wpdb->get_results("SELECT * FROM $raiseComplaint_table_name", ARRAY_A);

    echo "<pre>";
    // print_r($complaints[0]);
    // Display the complaints table
    echo '<div class="wrap">';
    echo '<h2>Complaints</h2>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr>';
    echo '<th>ID</th>';
    echo '<th>Name</th>';
    echo '<th>Ticket Number</th>';
    echo '<th>Date of Register</th>';
    echo '<th>Serial Number</th>';
    echo '<th>Status</th>';
    echo '</tr></thead>';
    echo '<tbody>';

    foreach ($complaints as $complaint) {
        $complaintStatus = $complaint['complaintStatus'];
        $disabled = ($complaintStatus == 3) ? 'disabled' : '';
        echo '<tr>';
        echo '<td>' . $complaint['id'] . '</td>';
        echo '<td>' . $complaint['complaintname'] . '</td>';
        echo '<td>' . $complaint['complaintTicketsNumber'] . '</td>';
        echo '<td>' . $complaint['complaintdateofRgister'] . '</td>';
        echo '<td>' . $complaint['complaintserialnumber'] . '</td>';
        echo '<td>';
        echo '<form method="post">';
        echo '<input type="hidden" name="complaint_id" value="' . $complaint['id'] . '">';
        echo '<select name="new_status" onchange="this.form.submit()" ' . $disabled . '>';
        echo '<option value="1" ' . selected(1, $complaintStatus, false) . ' style="color: blue;">Request</option>';
        echo '<option value="2" ' . selected(2, $complaintStatus, false) . ' style="color: orange;">Progressing</option>';
        echo '<option value="3" ' . selected(3, $complaintStatus, false) . ' style="color: green;">Completed</option>';
        echo '</select>';
        echo '</form>';
        echo '</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';
    echo '</div>';
}



function display_warranty_cards()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'Warranty_card';

    // Fetch data from the table
    $data = $wpdb->get_results("SELECT * FROM $table_name");

    // Display the DataTable
    ?>
    <style>
        .TbodyRow {
            text-align: center !important;
        }

        .upload {
            text-align: center;
            font-size: 30px;
            padding-top: 20px;
            padding-bottom: 30px;
            background: #fcba11;
            font-weight: 600;
        }

        .hr {
            border-top: 10px solid #008036;
            margin-bottom: 20px;
        }

        .Active {
            background: green;
            padding: 9px;
            border-radius: 6px;
            color: white;
        }

        .InActive {
            background: dimgrey;
            padding: 9px;
            border-radius: 6px;
            color: white;
        }
    </style>
    <div class="wrap">
        <div class="upload">Serial Number List</div>
        <div class="hr"></div>
        <table id="warranty-cards-table" class="display">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Brand</th>
                    <th>Model Name</th>
                    <th>Model Number</th>
                    <th>Serial Number</th>
                    <th>Batch</th>
                    <th>Customer ID</th>
                    <th>Customer Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $row): ?>
                    <tr class="TbodyRow">
                        <td>
                            <?php echo $row->id; ?>
                        </td>
                        <td>
                            <?php echo $row->Brand; ?>
                        </td>
                        <td>
                            <?php echo $row->Model_Name; ?>
                        </td>
                        <td>
                            <?php echo $row->Model_Number; ?>
                        </td>
                        <td>
                            <?php echo $row->serialnumber; ?>
                        </td>
                        <td>
                            <?php echo $row->Batch; ?>
                        </td>
                        <td>
                            <?php echo $row->customer_id; ?>
                        </td>
                        <td>
                            <?php
                            if ($row->customer_status == 1) {
                                echo "<div class='Active'>Active</div>";
                            } else {
                                echo "<div class='InActive'>InActive</div>";
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script>
        jQuery(document).ready(function ($) {
            $('#warranty-cards-table').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ]
            });
        });
    </script>
    <?php
}
add_action('wp_ajax_update_customer_status', 'update_customer_status_callback');
add_action('wp_ajax_nopriv_update_customer_status', 'update_customer_status_callback');

function update_customer_status_callback()
{
    if (isset($_POST['customer_id']) && isset($_POST['new_status'])) {
        $customer_id = intval($_POST['customer_id']);
        $new_status = intval($_POST['new_status']);

        global $wpdb;
        $table_name = $wpdb->prefix . 'Warranty_card';

        // Update the customer status in the database
        $wpdb->update(
            $table_name,
            array('customer_status' => $new_status),
            array('id' => $customer_id),
            array('%d'),
            array('%d')
        );

        // Return a success response
        wp_send_json_success();
    } else {
        // Return an error response if data is missing
        wp_send_json_error();
    }
}
//short code form user form 

// Register a shortcode to display the update form
add_shortcode('update_warranty_form', 'display_update_warranty_form');

function display_update_warranty_form()
{
    ob_start(); // Start output buffering

    // if (isset($_POST['update_warranty'])) {
    //     // Handle the form submission
    //     handle_update_warranty();
    // }

    if (isset($_POST['raiseComplaint'])) {
        // Handle the form submission
        raiseComplaint();
    }
    // if (isset($_POST['TrackCompaint'])) {
    //     // Handle the form submission
    //     TrackCompaint();
    // }

    // Display the update form
    ?>
    <style>
        .warranty_container {
            width: 70%;
            margin: auto;
        }

        .wtittle {
            text-align: center;
        }

        .warranty_row {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .warranty_card {
            font-size: 17px;
            border: 1px solid;
            margin: 13px;
            padding: 7px;
            padding-right: 40px;
            border-radius: 6px;
            cursor: pointer;
        }

        .warranty_card input {
            cursor: pointer;
        }

        .warranty_card label {
            cursor: pointer;
        }

        .warrantymain {

            padding-top: 20px;
        }

        .warranty_btn {
            text-align: center;
            padding-top: 2rem;
        }

        .warranty_sub {
            width: 33%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background: #008036;
            color: white;
            font-size: 19px;

        }

        .warranty_btn :hover {
            box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;
            cursor: pointer;
        }

        #Registerpage {
            display: none;
        }

        .Check-Serial-number {
            border: 1px solid green;
            color: green;
        }

        #show-vaild-msg {
            display: none;
        }

        .ErrorVaildSerial {
            border: 1px solid red;
            color: red;
        }

        #RegisterWarrranty {
            display: none;
        }

        #verifyotpbtn {
            display: none;
        }

        #otp-verify {
            display: none;
        }

        #otp-verify_name {
            display: none;
        }

        #ReSendOTPbtn {
            display: none;
        }

        #OtpErrorThrow {
            color: red;
            font-size: 14px;
            display: none;
        }

        #Error-vaild-msg {
            display: none;
        }

        .radioActive {
            accent-color: green;
        }

        .warranty_registerno {
            padding: 20px;
            width: 50%;
            margin: auto;
            border: 1px solid #fcba11;
            margin-top: 15px;
            border-radius: 7px;
            background: #fbfbfd;
        }
    </style>
    <!-- //Home page login -->
    <div class="warranty_container" id="loginPage">
        <form id="warrantyForm">
            <div class="warrantymain">
                <div class="warranty_row">
                    <div class="warranty_card">
                        <input class="radioActive" type="radio" id="register" name="warrantydetail" value="register">
                        <label for="register">Register</label>
                    </div>
                    <div class="warranty_card">
                        <input class="radioActive" type="radio" id="download" name="warrantydetail" value="download">
                        <label for="download">Download</label>
                    </div>
                    <div class="warranty_card">
                        <input class="radioActive" type="radio" name="warrantydetail" id="raisecomplaint"
                            value="raisecomplaint">
                        <label for="raisecomplaint">Raise Complaint</label>
                    </div>
                </div>
            </div>
            <div class="warranty_btn">
                <input class="warranty_sub" type="submit">
            </div>
        </form>
        <script>
            document.getElementById("warrantyForm").addEventListener("submit", function (event) {
                event.preventDefault(); // Prevent the form from actually submitting

                // Get the selected radio button value
                var selectedValue = document.querySelector('input[name="warrantydetail"]:checked').value;
                if (selectedValue == "register") {
                    document.getElementById("Registerpage").style.display = "block";
                    document.getElementById("loginPage").style.display = "none";
                }
                else if (selectedValue == "download") {
                    document.getElementById("Registerpage").style.display = "none";
                    document.getElementById("loginPage").style.display = "none";
                    document.getElementById("DownloadPageShow").style.display = "block";
                }
                else if (selectedValue == "raisecomplaint") {
                    document.getElementById("Registerpage").style.display = "none";
                    document.getElementById("loginPage").style.display = "none";
                    document.getElementById("RaiseCompaintPgae").style.display = "block"
                    document.getElementById("DownloadPageShow").style.display = "none";
                }
                else {
                    alert("Coming Soon");
                }
            });
        </script>
    </div>
    <!-- End Of Home Page Login  -->
    <!-- Show the vaild serial number  -->
    <div class="container" style="text-align: center;" id="show-vaild-msg">
        <div>
            <img src="http://localhost/marvel/wp-content/uploads/2023/09/approval.png" />
        </div>
        <div>You have Purchased a 100% genuine</div>
        <div>MARVEL TALL TUBULAR Battery</div>
    </div>
    <div class="container" style="text-align: center;" id="Error-vaild-msg">
        <div>
            <img src="http://localhost/marvel/wp-content/uploads/2023/09/close.png" />
        </div>
        <div style="color:red;">You have Serial Number is not vaild Please ReEnter Serial Number OR Contact your Dealer
        </div>
        <div>MARVEL TALL TUBULAR Battery</div>
    </div>
    <!-- end the vaild serial number  -->
    <!-- Register Page  -->
    <div class="wrap" id="Registerpage">
        <style>
            .warranty_container_Mu {
                width: 100%;
                padding: 10px;
                background: #fbfbfd;
                ;
                /* background: red; */
                background: #fbfbfd;
            }

            .warranty_card_box_Mu {
                width: 50%;
                padding: 20px;
                /* margin: auto; */
                /* border: 1px solid #fcba11; */
                border-radius: 7px;

            }

            .warranty_row_1 {
                display: flex;
                flex-wrap: wrap;
                border: 1px solid gray;
                border-radius: 7px;
            }

            .warranty_row_2 {
                display: flex;
                flex-wrap: wrap;
            }

            .warranty_container_Mu input {
                width: 100%;
                padding: 5px;
                border: 1px solid gray;
                border-radius: 4px;
            }

            .warranty_container_Mu label {
                font-size: 18px;
                font-weight: 600;
            }

            .Text_Center {
                text-align: center;
            }

            .OTPSENDBTN_mu:disabled,
            .OTPSENDBTN_mu[disabled] {
                border: 1px solid #999999;
                background-color: #cccccc;
                color: #666666;
            }

            .OTPSENDBTN_mu {
                width: 20%;
                padding: 15px;
                border: none;
                background: green;
                color: white;
                font-size: 18px;
                font-weight: 600;
                border-radius: 8px;
                margin-top: 15px;
            }

            .OTPSENDBTN_mu :hover {
                box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;
                cursor: pointer;
            }

            .EnterOTP {
                margin-left: 26px;
                width: 100%;
                font-size: 18px;
                font-weight: 700;
                width: 50%;
                margin: auto;
                padding: 15px;

            }

            .ResendBTN {
                margin-left: auto;
                margin-right: auto;
            }

            .DownloadResendBTN {
                margin-left: auto;
                margin-right: auto;
            }

            #ReSendOTPbtn {
                display: block;
                margin: 13px;
                padding: 9px;
                width: 100%;
                border: none;
                border-radius: 5px;
                background: #000076;
                color: white;
                display: none;
            }

            #DownloadReSendOTPbtn {
                display: block;
                margin: 13px;
                padding: 9px;
                width: 100%;
                border: none;
                border-radius: 5px;
                background: #000076;
                color: white;
                display: none;
            }

            #verifyotpbtn {
                display: block;
                margin: 13px;
                padding: 9px;
                width: 100%;
                border: none;
                border-radius: 5px;
                background: green;
                color: white;
                display: none;
            }

            #Downloadverifyotpbtn {
                display: block;
                margin: 13px;
                padding: 9px;
                width: 100%;
                border: none;
                border-radius: 5px;
                background: green;
                color: white;
                display: none;
            }

            #RegisterWarrranty {
                margin: 15px;
                margin-left: auto;
                margin-right: auto;
                padding: 15px;
                width: 20%;
                border: none;
                /* margin: 10px; */
                border-radius: 5px;
                font-size: 18px;
                background: green;
                color: white;
            }

            .successfully {
                text-align: center;
                background: green;
                padding: 16px;
                border-radius: 10px;
                color: white;
                font-size: 20px;
                display: none;
            }

            .errorsuccessfully {
                color: red;
            }

            .reg_in {
                width: 47%;
                padding: 6px;
                border-radius: 5px;
                border: 1px solid black;

            }

            .Complaint_main {
                padding: 20px;
                width: 50%;
                margin: auto;
                border: 1px solid #fcba11;
                margin-top: 15px;
                border-radius: 7px;
                background: #fbfbfd;

            }

            .down_mg {
                padding: 20px;
                width: 50% !important;
                margin: auto !important;
                border: 1px solid #fcba11 !important;
                margin-top: 15px !important;
                border-radius: 7px !important;
                background: #fbfbfd !important;

            }
        </style>
        <form method="post">
            <div class="warranty_container_Mu">
                <div class="warranty_row_2">
                    <div class="warranty_card_box_Mu">
                        <div class="warranty_row_1">
                            <div class="warranty_card_box_Mu war_lab"><label for="customer_name">Name:</label></div>
                            <div class="warranty_card_box_Mu"><input type="text" name="customer_name" id="customer_name"
                                    required></div>
                        </div>
                    </div>
                    <div class="warranty_card_box_Mu">
                        <div class="warranty_row_1">
                            <div class="warranty_card_box_Mu war_lab"><label for="phone_number">Phone Number:</label></div>
                            <div class="warranty_card_box_Mu"><input type="text" name="phone_number" id="phone_number"
                                    required></div>
                        </div>
                    </div>
                    <div class="warranty_card_box_Mu">
                        <div class="warranty_row_1">
                            <div class="warranty_card_box_Mu war_lab">
                                <label for="customer_mail">Email:</label>
                            </div>
                            <div class="warranty_card_box_Mu">
                                <input type="email" name="customer_mail" id="customer_mail" required>
                            </div>
                        </div>
                    </div>
                    <div class="warranty_card_box_Mu">
                        <div class="warranty_row_1">
                            <div class="warranty_card_box_Mu">
                                <label for="serial_number">Serial Number:</label>
                            </div>
                            <div class="warranty_card_box_Mu">
                                <input type="text" name="serial_number" id="serial_number" required>
                                <div id="serial_number_status"></div>
                            </div>
                        </div>
                    </div>
                    <div class="warranty_card_box_Mu">
                        <div class="warranty_row_1">
                            <div class="warranty_card_box_Mu">
                                <label for="date_of_sale">Date Of Sale:</label>
                            </div>
                            <div class="warranty_card_box_Mu">
                                <input type="date" name="date_of_sale" id="date_of_sale" required>
                            </div>
                        </div>
                    </div>
                    <div class="warranty_card_box_Mu">
                        <div class="warranty_row_1">
                            <div class="warranty_card_box_Mu">
                                <label for="customer_location">Location:</label>
                            </div>
                            <div class="warranty_card_box_Mu">
                                <input type="text" name="customer_location" id="customer_location" required>
                            </div>
                        </div>
                    </div>
                    <div class="warranty_card_box_Mu">
                        <div class="warranty_row_1">
                            <div class="warranty_card_box_Mu">
                                <label for="Dealers">Dealers:</label>
                            </div>
                            <div class="warranty_card_box_Mu">
                                <select name="Dealers" id="Dealers">
                                    <option value="Madurai">Madurai</option>
                                    <option value="Chennai">Chennai</option>
                                    <option value="Chennai">Chennai</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <input id="RegisterWarrranty" type="submit" name="update_warranty" value="Register Warranty">
        </form>
        <div class="EnterOTP">
            <label id="otp-verify_name" for="otp-verify">Enter Your OTP:</label>
            <input type="text" name="otp-verify" id="otp-verify" required>
        </div>
        <p id="OtpErrorThrow">Please Enter Your Vaild Otp Number !</p>
        <div class="Text_Center"><button class="OTPSENDBTN_mu" id="SendOTPbtn" onclick="otpcheck(1)">Send OTP</button></div>
        <div style="display: flex;">
            <div class="ResendBTN">
                <button id="ReSendOTPbtn" onclick="otpcheck(1)">ReSend OTP</button>
                <button id="verifyotpbtn" onclick="otpcheck(2)">verify OTP</button>
            </div>
        </div>
    </div>
    <!-- ///////////////////////////////////////////////////// Download Warranty Page //////////////////////////////////////////////////////////////////////// -->
    <style>
        #Downloadotp-verify {
            display: none;
        }

        #Downloadotp-verify_name {
            display: none;
        }

        #DownloadOtpErrorThrow {
            display: none;
        }

        #DownloadPageShow {
            display: none;
        }
    </style>
    <section id="DownloadPageShow">
        <div class="warranty_card_box_Mu down_mg">
            <label>Enter Your Register Serial Number</label>
            <input class="reg_in" type="text" name="Download_serial_number" id="Download_serial_number" required>
            <div id="serial_number_status"></div>
        </div>
        <div id="Downloadotp-verify_name" class="Complaint_main">
            <label id="Downloadotp-verify_name" for="Downloadotp-verify">Enter Your OTP No:</label>
            <input type="text" name="Downloadotp-verify" id="Downloadotp-verify" required>
        </div>
        <div class="warranty_registerno">Your Register Number :<span id="ShowRegisterNumber"></span><input
                id="ShowRegisterNumberinput" type="hidden" />
        </div>
        <p id="DownloadOtpErrorThrow">Please Enter Your Vaild Otp Number !</p>
        <div class="Text_Center"><button class="OTPSENDBTN_mu" id="DownloadSendOTPbtn" onclick="Downloadotpcheck(1)">Send
                OTP</button></div>
        <div style="display: flex;">
            <div class="DownloadResendBTN">
                <button id="DownloadReSendOTPbtn" onclick="Downloadotpcheck(1)">ReSend OTP</button>
                <button id="Downloadverifyotpbtn" onclick="Downloadotpcheck(2)">verify OTP</button>
            </div>
        </div>

    </section>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }

        .cust_list_tab {
            width: 70%;
        }

        table,
        th,
        td {

            border: 1px solid black;
        }

        th,
        td {
            /* padding: 8px; */
            /* text-align: left; */
        }

        th {
            background-color: #f2f2f2;
            padding: 10px;
            font-size: 18px;
        }

        td {
            padding: 5px;
            font-size: 16px;
        }

        .raise-complaint-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 8px 12px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            margin: 4px 2px;
            cursor: pointer;
        }

        #modelDetails {
            display: none;
        }

        #ShowPDFData {
            width: 100%;
            margin: auto;
            background: #fcba11;
            padding: 16px;
            font-size: 18px;
        }

        .pdfNameLabel {
            width: 50%;
        }

        #mainPdfFrame {
            display: none;
        }
    </style>

    <div id="modelDetails">
        <div id="mainPdfFrame">
            <div id="ShowPDFData">
                <div>
                    <span class="pdfNameLabel">Name :</span><span id="pdfName"></span>
                </div>
                <div>
                    <span>Email ID :</span><span id="pdfemail"></span>
                </div>
                <div>
                    <span>Phone Number :</span><span id="pdfphonenumber"></span>
                </div>
                <div>
                    <span>Serial Number :</span><span id="pdfserialnumber"></span>
                </div>
                <div>
                    <span>Customer ID :</span><span id="pdfcustomer_ID"></span>
                </div>
                <div>
                    <span>Date of Sale :</span><span id="pdfdateofsale"></span>
                </div>
                <div>
                    <span>Date of Register</span><span id="pdfdateofRgister"></span>
                </div>
                <div>
                    <span>Location :</span><span id="pdflocation"></span>
                </div>
                <div>
                    <span>Dealer :</span><span id="pdfdealer"></span>
                </div>
            </div>
        </div>

        <table id="customer-table" class="cust_list_tab" border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Phone Number</th>
                    <th>Email</th>
                    <th>Serial Number</th>
                    <th>Customer ID</th>
                    <th>Date of Sale</th>
                    <th>Date of Register</th>
                    <th>Location</th>
                    <th>Dealer</th>
                    <th>Download Warranty Card</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
    <!-- ////////////////////////////////////////////////////////////////////////Print Register successfully Msg ///////////////////////////////////////////////////////////////// -->
    <div id="PrintregisterSuccesse">
        
    </div>
    <!-- //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.js"></script>
    <script>
        jQuery(document).ready(function ($) {
            $('#RegisterWarrranty').on('click', function (e) {
                e.preventDefault();

                // Collect form data
                var formData = {
                    'customer_name': $('#customer_name').val(),
                    'phone_number': $('#phone_number').val(),
                    'customer_mail': $('#customer_mail').val(),
                    'serial_number': $('#serial_number').val(),
                    'date_of_sale': $('#date_of_sale').val(),
                    'customer_location': $('#customer_location').val(),
                    'Dealers': $('#Dealers').val()
                };

                // AJAX request
                $.ajax({
                    type: 'POST',
                    url: Cityajaxurl, // WordPress AJAX URL
                    data: {
                        action: 'register_warranty',
                        formData: formData
                    },
                    success: function (response) {
                        if (response.success) {



                            // Registration was successful
                            // Assuming you have a div with the ID "registrationDetails" for displaying the data
                            var registrationDetailsDiv = document.getElementById('registrationDetails');

                            // Create an HTML string to display the data
                            var html = '<h3>Registration Details:</h3>';
                            html += '<p><strong>Name:</strong> ' + response.data.customer_name + '</p>';
                            html += '<p><strong>Phone Number:</strong> ' + response.data.phone_number + '</p>';
                            html += '<p><strong>Email:</strong> ' + response.data.customer_mail + '</p>';
                            html += '<p><strong>Serial Number:</strong> ' + response.data.serial_number + '</p>';
                            html += '<p><strong>Date of Sale:</strong> ' + response.data.date_of_sale + '</p>';
                            html += '<p><strong>Date of Registration:</strong> ' + response.data.dateofRgister + '</p>';
                            html += '<p><strong>Location:</strong> ' + response.data.customer_location + '</p>';
                            html += '<p><strong>Dealers:</strong> ' + response.data.Dealers + '</p>';
                            html += '<p><strong>Customer ID:</strong> ' + response.data.customer_ID + '</p>';

                            // Set the HTML content of the div
                            registrationDetailsDiv.innerHTML = html;
                        } else {
                            // Registration encountered an error
                            console.log(response.data);
                        }
                    }
                });
            });
        });

    </script>
    <script>
        var stroeOTP = 0;
        function otpcheck(methodcall) {
            var customer_name = document.getElementById("customer_name").value;
            var phone_number = document.getElementById("phone_number").value;
            var customer_mail = document.getElementById("customer_mail").value;
            var serial_number = document.getElementById("serial_number").value;
            if (phone_number != "" && serial_number != "") {
                // Call the function to generate an OTP
                if (methodcall == 1) {
                    // const otp = Math.floor(1000 + Math.random() * 9000);
                    stroeOTP = 1234;
                    const otp = 1234;
                    var xhr = new XMLHttpRequest();
                    xhr.withCredentials = true;
                    xhr.addEventListener("readystatechange", function () {
                        if (this.readyState === 4) {
                            console.log(this.responseText);
                        }
                    });
                    xhr.open("POST", "http://smsserver9.creativepoint.in/api.php?username=marvel&password=132654&to=" + phone_number + "&from=MARVEL&message=Dear%20Customer%2C%20Please%20Enter%20Your%20OTP%20" + otp + "%20%20to%20Login%20into%20your%20Customer%20Dashboard%20%2C%20Contact%20us-%20Email%3Asupport%40marvelbatteries.com&PEID=1701158134826722805&templateid=1707161588809294021");
                    // WARNING: Cookies will be stripped away by the browser before sending the request.
                    xhr.setRequestHeader("Cookie", "PHPSESSID=pcedbgoe1dailflod1548ghqm3");
                    xhr.send();
                    document.getElementById("SendOTPbtn").style.display = "none";
                    document.getElementById("verifyotpbtn").style.display = "block";
                    document.getElementById("otp-verify").style.display = "block";
                    document.getElementById("otp-verify_name").style.display = "block";
                    document.getElementById("ReSendOTPbtn").style.display = "block";
                    document.getElementById("RegisterWarrranty").style.display = "none";
                }
                else if (methodcall == 2) {
                    var customer_OTP = document.getElementById("otp-verify").value;
                    if (stroeOTP == customer_OTP) {
                        document.getElementById("RegisterWarrranty").style.display = "block";
                        document.getElementById("SendOTPbtn").style.display = "none";
                        document.getElementById("verifyotpbtn").style.display = "none";
                        document.getElementById("otp-verify").style.display = "none";
                        document.getElementById("otp-verify_name").style.display = "none";
                        document.getElementById("ReSendOTPbtn").style.display = "none";
                        document.getElementById("OtpErrorThrow").style.display = "none";
                        stroeOTP = 0;
                    }
                    else {
                        document.getElementById("OtpErrorThrow").style.display = "block";
                    }

                }
            }
            else {
                var emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/; // Validates email format
                // Validate email
                if (!emailRegex.test(customer_mail)) {
                    alert("Please enter a valid email address.");
                    return;
                }
            }
        }
        jQuery(document).ready(function ($) {
            $('#serial_number').blur('click', function () {
                var Serial_number = document.getElementById("serial_number").value;
                if (Serial_number == "" || Serial_number == null) {
                }
                else {
                    // Make an AJAX request to update the status
                    $.ajax({
                        type: 'POST',
                        url: Cityajaxurl, // WordPress AJAX URL
                        data: {
                            action: 'Check_serial_number',
                            Serial_number: Serial_number,
                        },
                        success: function (response) {
                            var jsonObject = JSON.parse(response);
                            var messageValue = jsonObject.message;
                            console.log(messageValue);
                            if (messageValue.length != 0) {
                                var serial_numberClass = document.getElementById("serial_number");
                                // Add a class to the element
                                serial_numberClass.classList.add("Check-Serial-number");
                                // Remove a class from the element
                                document.getElementById("show-vaild-msg").style.display = "block";
                                document.getElementById("Error-vaild-msg").style.display = "none";
                                serial_numberClass.classList.remove("ErrorVaildSerial");
                                document.getElementById("SendOTPbtn").disabled = false;
                            } else {
                                var serial_numberClass = document.getElementById("serial_number");
                                document.getElementById("show-vaild-msg").style.display = "none";
                                document.getElementById("Error-vaild-msg").style.display = "block";
                                serial_numberClass.classList.remove("Check-Serial-number");
                                serial_numberClass.classList.add("ErrorVaildSerial");
                                document.getElementById("SendOTPbtn").disabled = true;
                            }
                        }
                    });
                }
            });
        });
        ///////////////////////////////////////////////////////////////Check Download Serial_number//////////////////////////////////////////////////////////////////////////
        jQuery(document).ready(function ($) {
            $('#Download_serial_number').blur('click', function () {
                var Serial_number = document.getElementById("Download_serial_number").value;
                if (Serial_number == "" || Serial_number == null) {
                }
                else {
                    // Make an AJAX request to update the status
                    $.ajax({
                        type: 'POST',
                        url: Cityajaxurl, // WordPress AJAX URL
                        data: {
                            action: 'Check_serial_number',
                            Download_serial_number: Serial_number,
                        },
                        success: function (response) {
                            var jsonObject = JSON.parse(response);
                            var messageValue = jsonObject.message;
                            var CustomerDetailsValue = jsonObject.customer;
                            if (messageValue.length != 0) {
                                var serial_numberClass = document.getElementById("Download_serial_number");
                                // Add a class to the element
                                serial_numberClass.classList.add("Check-Serial-number");
                                // Remove a class from the element
                                document.getElementById("show-vaild-msg").style.display = "block";
                                document.getElementById("Error-vaild-msg").style.display = "none";
                                serial_numberClass.classList.remove("ErrorVaildSerial");
                                document.getElementById("SendOTPbtn").disabled = false;
                                createUI(messageValue, CustomerDetailsValue);
                            } else {
                                var serial_numberClass = document.getElementById("Download_serial_number");
                                document.getElementById("show-vaild-msg").style.display = "none";
                                document.getElementById("Error-vaild-msg").style.display = "block";
                                serial_numberClass.classList.remove("Check-Serial-number");
                                serial_numberClass.classList.add("ErrorVaildSerial");
                                document.getElementById("SendOTPbtn").disabled = true;
                                createUI(messageValue, CustomerDetailsValue);
                            }

                        }
                    });
                }
            });
        });
        function createUI(messageValue, CustomerDetailsValue) {
            if (messageValue.length != 0) {
                document.getElementById("ShowRegisterNumber").innerText = CustomerDetailsValue[0].phonenumber;
                document.getElementById("ShowRegisterNumberinput").value = CustomerDetailsValue[0].phonenumber;
                const mergedJSON = mergeJSONObjects(messageValue, CustomerDetailsValue);
                function mergeJSONObjects(obj1, obj2) {
                    const merged = { ...obj1 };

                    for (const key in obj2) {
                        if (obj2.hasOwnProperty(key)) {
                            merged[key] = obj2[key];
                        }
                    }

                    return merged;
                }
                const jsonData = mergedJSON;
                console.log(jsonData);
                function createTableRow(data) {
                    const row = document.createElement("tr");

                    for (const key in data) {
                        if (data.hasOwnProperty(key)) {
                            const cell = document.createElement("td");
                            cell.textContent = data[key];
                            row.appendChild(cell);
                        }
                    }

                    // Create the new column cell (empty for now)
                    const newColumnCell = document.createElement("td");
                    row.appendChild(newColumnCell);

                    // Create a button in the new column cell
                    const newColumnBtn = document.createElement("button");
                    newColumnBtn.textContent = "Download";
                    newColumnCell.appendChild(newColumnBtn);
                    newColumnBtn.addEventListener('click', () => {
                        DOwnloadPDFWarranty();
                    });

                    // Create the "Raise Complaint" button cell (same as before)
                    const actionsCell = document.createElement("td");
                    const raiseComplaintBtn = document.createElement("button");
                    raiseComplaintBtn.textContent = "Raise Complaint";
                    raiseComplaintBtn.onclick = function () {
                        OpenModelFunction(data);
                    };
                    raiseComplaintBtn.className = "raise-complaint-btn";
                    actionsCell.appendChild(raiseComplaintBtn);
                    row.appendChild(actionsCell);

                    return row;
                }

                // Function to populate the table with customer data
                function populateTable() {
                    const tableBody = document.querySelector("#customer-table tbody");

                    for (const key in jsonData) {
                        if (jsonData.hasOwnProperty(key)) {
                            const customerData = jsonData[key];
                            const row = createTableRow(customerData);
                            tableBody.appendChild(row);
                        }
                    }
                }

                // Call the populateTable function to initialize the table
                populateTable();
                // console.log(CustomerDetailsValue);
                const container = document.createElement('div');
                container.className = 'customer-container';

                for (const key in jsonData) {
                    if (jsonData.hasOwnProperty(key)) {
                        const customer = jsonData[key];
                        document.getElementById("pdfName").innerText = customer.name;
                        document.getElementById("pdfphonenumber").innerText = customer.phonenumber;
                        document.getElementById("pdfemail").innerText = customer.email;
                        document.getElementById("pdfserialnumber").innerText = customer.serialnumber;
                        document.getElementById("pdfcustomer_ID").innerText = customer.customer_ID;
                        document.getElementById("pdfdateofsale").innerText = customer.dateofsale;
                        document.getElementById("pdfdateofRgister").innerText = customer.dateofRgister;
                        document.getElementById("pdflocation").innerText = customer.location;
                        document.getElementById("pdfdealer").innerText = customer.dealer;
                        // const customerDiv = document.createElement('div');
                        // customerDiv.className = 'customer';
                        // customerDiv.innerHTML = `
                        //                                 <p id="pdfName">Name : ${customer.name}</h2>
                        //                                 <p>Phone Number: ${customer.phonenumber}</p>
                        //                                 <p>Email: ${customer.email}</p>
                        //                                 <p>Serial Number: ${customer.serialnumber}</p>
                        //                                 <p>Customer ID: ${customer.customer_ID}</p>
                        //                                 <p>Date of Sale: ${customer.dateofsale}</p>
                        //                                 <p>Date of Register: ${customer.dateofRgister}</p>
                        //                                 <p>Location: ${customer.location}</p>
                        //                                 <p>Dealer: ${customer.dealer}</p>
                        //                             `;
                        // container.appendChild(customerDiv);
                    }
                }

                document.getElementById("ShowPDFData").appendChild(container);
            }
        }
        function DOwnloadPDFWarranty() {
            var userName = document.getElementById("pdfName").innerText;
            let makepdf = document.getElementById("ShowPDFData");
            const pdfOptions = {
                filename: userName + ".pdf",
            };
            html2pdf().from(makepdf).set(pdfOptions).save();
        }
        var stroeOTP = 0;
        function Downloadotpcheck(methodcall) {
            var phone_number = document.getElementById("ShowRegisterNumberinput").value;
            console.log(phone_number);
            if (phone_number != "" || phone_number != 0) {
                // Call the function to generate an OTP
                if (methodcall == 1) {

                    // const otp = Math.floor(1000 + Math.random() * 9000);
                    const otp = 1234;
                    stroeOTP = 1234;
                    var xhr = new XMLHttpRequest();
                    xhr.withCredentials = true;
                    xhr.addEventListener("readystatechange", function () {
                        if (this.readyState === 4) {
                            // console.log(this.responseText);
                        }
                    });
                    xhr.open("POST", "http://smsserver9.creativepoint.in/api.php?username=marvel&password=132654&to=" + phone_number + "&from=MARVEL&message=Dear%20Customer%2C%20Please%20Enter%20Your%20OTP%20" + otp + "%20%20to%20Login%20into%20your%20Customer%20Dashboard%20%2C%20Contact%20us-%20Email%3Asupport%40marvelbatteries.com&PEID=1701158134826722805&templateid=1707161588809294021");
                    // WARNING: Cookies will be stripped away by the browser before sending the request.
                    xhr.setRequestHeader("Cookie", "PHPSESSID=pcedbgoe1dailflod1548ghqm3");
                    xhr.send();
                    document.getElementById("DownloadSendOTPbtn").style.display = "none";
                    document.getElementById("Downloadverifyotpbtn").style.display = "block";
                    document.getElementById("DownloadReSendOTPbtn").style.display = "block";
                    document.getElementById("Downloadotp-verify").style.display = "block";
                    document.getElementById("Downloadotp-verify_name").style.display = "block";
                }
                else if (methodcall == 2) {
                    var customer_OTP = document.getElementById("Downloadotp-verify").value;
                    if (stroeOTP == customer_OTP) {
                        document.getElementById("DownloadSendOTPbtn").style.display = "none";
                        document.getElementById("Downloadverifyotpbtn").style.display = "none";
                        document.getElementById("DownloadReSendOTPbtn").style.display = "none";
                        document.getElementById("show-vaild-msg").style.display = "none";
                        document.getElementById("DownloadPageShow").style.display = "none";
                        document.getElementById("modelDetails").style.display = "block";
                        stroeOTP = 0;
                    }
                    else {
                        document.getElementById("DownloadOtpErrorThrow").style.display = "block";
                    }

                }
            }
        }
        ///////////////////////////////////////////////**************************************** *///////////////////////////////////////////////
    </script>
    <!-- End Of  Register Page  -->
    <!-- //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// -->
    <section>
        <style>
            /* Styles for the modal container */
            .Complaintmodal {
                display: none;
                position: fixed;
                z-index: 1;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.7);
            }

            /* Styles for the modal content */
            .Complaintmodal-content {
                background-color: #fff;
                margin: auto;
                padding: 10px;
                border: 1px solid #888;
                width: 50%;
                box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
                text-align: center;
                background: #fbfbfd;
                border-radius: 8px;
                margin-top: 3rem;
                padding-bottom: 2rem;
            }

            /* Style for the close button */
            .close {
                position: absolute;
                right: 10px;
                top: 10px;
                font-size: 20px;
                font-weight: bold;
                cursor: pointer;
            }

            .complaint_new {
                width: 80%;
                padding: 10px;
                border: 1px solid #fcba11;
                margin: auto;
                border-radius: 7px;
            }

            .compalint_se {
                width: 45%;
                padding: 7px;
                border-radius: 5px;
            }

            .complaint_custext {
                width: 80%;
                padding: 10px;
                border: 1px solid #fcba11;
                margin: auto;
                border-radius: 7px;
                margin-top: 1.5rem;
            }

            .complaint_sbtn {
                width: 46%;
                padding: 10px;
                color: white;
                background: green;
                border: none;
                border-radius: 6px;
                font-size: 18px;
                margin-top: 1.5rem
            }
        </style>
        <div id="ComplaintmyModal" class="Complaintmodal">
            <div class="Complaintmodal-content">
                <span class="close" id="ComplaintcloseModalBtn">&times;</span>
                <h4>Raise Your Complaint</h4>
                <form id="ComplaintmyForm" method="post">
                    <div class="complaint_new">
                        <label for="ComplaintList">Select an option:</label>
                        <input id="complaintname" type="hidden" name="complaintname" />
                        <input id="complaintphonenumber" type="hidden" name="complaintphonenumber" />
                        <input id="complaintemail" type="hidden" name="complaintemail" />
                        <input id="complaintserialnumber" type="hidden" name="complaintserialnumber" />
                        <input id="complaintcustomer_ID" type="hidden" name="complaintcustomer_ID" />
                        <input id="complaintdateofRgister" type="hidden" name="complaintdateofRgister" />
                        <input id="complaintdateofsale" type="hidden" name="complaintdateofsale" />
                        <input id="complaintdealer" type="hidden" name="complaintdealer" />
                        <input id="complaintlocation" type="hidden" name="complaintlocation" />

                        <select class="compalint_se" id="ComplaintList" name="ComplaintList" required>
                            <option value="option1">Option 1</option>
                            <option value="option2">Option 2</option>
                            <option value="option3">Option 3</option>
                        </select>
                    </div>
                    <div class="complaint_custext">
                        <label for="ComplaintListText">Enter Your Complaint:</label>
                        <textarea id="ComplaintListText" name="ComplaintListText" rows="4" cols="50" required></textarea>
                    </div>
                    <button class="complaint_sbtn" type="submit" name="raiseComplaint">Submit</button>
                </form>
            </div>
        </div>
        <script>
            function OpenModelFunction(UserData) {
                // Get the modal and button elements
                const Complaintmodal = document.getElementById('ComplaintmyModal');
                const ComplaintopenModalButton = document.getElementById('ComplaintopenModalBtn');
                const ComplaintcloseModalBtn = document.getElementById('ComplaintcloseModalBtn');
                const ComplaintmyForm = document.getElementById('ComplaintmyForm');
                document.getElementById('complaintname').value = UserData.name;
                document.getElementById('complaintphonenumber').value = UserData.phonenumber;
                document.getElementById('complaintemail').value = UserData.email;
                document.getElementById('complaintserialnumber').value = UserData.serialnumber;
                document.getElementById('complaintcustomer_ID').value = UserData.customer_ID;
                document.getElementById('complaintdateofRgister').value = UserData.dateofRgister;
                document.getElementById('complaintdateofsale').value = UserData.dateofsale;
                document.getElementById('complaintdealer').value = UserData.dealer;
                document.getElementById('complaintlocation').value = UserData.location;
                Complaintmodal.style.display = 'block';
                // Close the modal when the close button is clicked
                ComplaintcloseModalBtn.addEventListener('click', () => {
                    Complaintmodal.style.display = 'none';
                });
                // Close the modal when clicking outside the modal content
                window.addEventListener('click', (event) => {
                    if (event.target === Complaintmodal) {
                        Complaintmodal.style.display = 'none';
                    }
                });
            }
        </script>
    </section>
    <!-- ////////////////////////////////////**************************************************************************///////////////////////// -->
    <!-- /////////////////////////////////////////////////////////////////////Raise Complaint Section ////////////////////////////////////////////////////////////////// -->
    <style>
        .raiseRow {

            display: flex;
            flex-wrap: wrap;
        }

        .raisecomplaint2buttton {
            margin: auto;
        }

        #CheckTicketNumberStatus {
            display: none;
        }

        #RaiseCompaintPgae {
            display: none;
        }

        #ShowTrackTicketsNumber {
            display: none;
        }

        .raisecomplaint2 {
            width: 60%;
            padding: 15px;
            margin: auto;
        }

        .raise_btnm {
            width: 100%;
            padding: 20px;
            border: none;
            border-radius: 7px;
            background: green;
            color: white;
            font-size: 18px;
        }

        #CheckTicketNumberStatus {
            width: 50%;
            padding: 44px;
            border: 1px solid #fcba11;
            margin: auto;
            border-radius: 9px;
        }

        .ticket_in {
            padding: 15px;
            width: 50%;
            margin: auto;
            border-radius: 9px
        }

        .raise_confi_btn {
            padding: 10px;
            width: 37%;
            border-radius: 7px;
            text-align: center;
            color: white;
            font-size: 18px;
            background: green;
            margin-top: 2.5rem;
            border: none;
            margin-left: 10rem;
        }

        #ErrorTicketNumber {
            display: none;
            color: red;
            font-size: 18px;
            padding: 10px;
        }
    </style>
    <section id="RaiseCompaintPgae">
        <div class="raisecomplaint2">
            <div class="raiseRow">
                <div class="raisecomplaint2buttton">
                    <button class="raise_btnm">Raise Complaint</button>
                </div>
                <div class="raisecomplaint2buttton">
                    <button class="raise_btnm" onclick="Showcomplaintview()">Track Complaint</button>
                </div>
            </div>
        </div>
    </section>
    <section id="CheckTicketNumberStatus">
        <!-- <form method="post"> -->
        <div id="ErrorTicketNumber">Your ticket number is invalid !</div>
        <label>Enter Your Ticket Number</label>
        <input class="ticket_in" type="text" placeholder="Enter Your ticket Number" id="ticketNumber"
            name="ticketNumber" /><br>
        <button class="raise_confi_btn" id="Trackcompaintsubmit">Submit</button>
        <!-- </form> -->
    </section>
    <section id="ShowTrackTicketsNumber">
        <div>Customer Nmae:</div>
        <div id="TrackCustomerName"></div>
        <div>Customer Phone Number:</div>
        <div id="TrackCustomerphonenumber"></div>
        <div>Customer Email:</div>
        <div id="TrackCustomeremail"></div>
        <div>Serial Number:</div>
        <div id="TrackCustomerSerialNumber"></div>
        <div>Complaint Register Date:</div>
        <div id="TrackCustomercomplaintofregisterdate"></div>
        <div>Ticket Number:</div>
        <div id="TrackCustomerTicketNumber"></div>
        <div>Ticket Status:</div>
        <div id="TrackCustomerTicketStatus"></div>
        <button onclick="goHome()">Back Home</button>
    </section>
    <script>
        function Showcomplaintview() {
            document.getElementById("RaiseCompaintPgae").style.display = "none";
            document.getElementById("CheckTicketNumberStatus").style.display = "block";
        }
        // Add this code to your WordPress plugin or theme's JavaScript file

        jQuery(document).ready(function ($) {
            $('#Trackcompaintsubmit').on('click', function () {
                var TicketNumber = $('#ticketNumber').val();
                if (TicketNumber === "" || TicketNumber === null) {
                    // Handle the case when Serial_number is empty
                } else {
                    // Make an AJAX request to update the status
                    $.ajax({
                        type: 'POST',
                        url: Cityajaxurl, // WordPress AJAX URL
                        data: {
                            action: 'track_complaint', // Update this to match your action name
                            RaiseTicketNumber: TicketNumber,
                        },
                        success: function (response) {
                            var jsonObject = JSON.parse(response);
                            var messageValue = jsonObject.message;

                            var responseStatus = jsonObject.status;

                            console.log(responseStatus);
                            console.log(messageValue);
                            if (responseStatus == "Fail") {
                                document.getElementById("ErrorTicketNumber").style.display = "block";
                            }
                            else if (responseStatus == "success") {
                                var Customerphonenumber = document.getElementById("TrackCustomerphonenumber");
                                var Customeremail = document.getElementById("TrackCustomeremail");
                                var CustomerSerialnumber = document.getElementById("TrackCustomerSerialNumber");
                                var Customercomplaintofregisterdate = document.getElementById("TrackCustomercomplaintofregisterdate");
                                var CustomerTicketNumber = document.getElementById("TrackCustomerTicketNumber");
                                var CustomerTicketStatus = document.getElementById("TrackCustomerTicketStatus");

                                document.getElementById("TrackCustomerName").innerText = messageValue[0].complaintname;
                                Customerphonenumber.innerText = messageValue[0].complaintphonenumber;
                                Customeremail.innerText = messageValue[0].complaintemail;
                                CustomerSerialnumber.innerText = messageValue[0].complaintserialnumber;
                                Customercomplaintofregisterdate.innerText = messageValue[0].complaintdateofRgister;
                                CustomerTicketNumber.innerText = messageValue[0].complaintTicketsNumber;
                                var CheckStatusTicketsnumbers = messageValue[0].complaintStatus;
                                if (CheckStatusTicketsnumbers == 1) {
                                    CustomerTicketStatus.innerText = "Request";
                                }
                                else if (CheckStatusTicketsnumbers == 2) {
                                    CustomerTicketStatus.innerText = "Progressing";
                                }
                                else if (CheckStatusTicketsnumbers == 3) {
                                    CustomerTicketStatus.innerText = "Completed";
                                }
                                document.getElementById("CheckTicketNumberStatus").style.display = "none";
                                document.getElementById("ShowTrackTicketsNumber").style.display = "block";
                            }
                        }
                    });
                }
            });
        });
        function goHome() {
            document.getElementById("ShowTrackTicketsNumber").style.display = "none";
            document.getElementById("loginPage").style.display = "block";
            location.reload();
        }
    </script>
    <!-- ////////////////////////////////////**************************************************************************///////////////////////// -->
    <?php
    return ob_get_clean(); // Return the buffered content
}
add_action('wp_ajax_Check_serial_number', 'Check_serial_number_callback');
add_action('wp_ajax_nopriv_Check_serial_number', 'Check_serial_number_callback');
function Check_serial_number_callback()
{
    if (isset($_POST['Serial_number'])) {
        $Serial_number = $_POST['Serial_number'];
        global $wpdb;

        $SearchSerialNumber = sanitize_text_field($Serial_number); // Sanitize the input to prevent SQL injection
        $table_name = $wpdb->prefix . 'Warranty_card';

        $query = $wpdb->prepare(
            "SELECT * FROM $table_name WHERE serialnumber = %s AND customer_status = 0",
            $SearchSerialNumber
        );
        $results = $wpdb->get_results($query);
        if (count($results) != 0) {
            $response = array('status' => 'success', 'message' => $results);
            echo json_encode($response);
        } else {
            $response = array('status' => 'success', 'message' => $results);
            echo json_encode($response);
        }
        // You can send a success response in various ways, such as echoing a JSON response        
    } else if (isset($_POST['Download_serial_number'])) {
        $Download_serial_number = $_POST['Download_serial_number'];
        global $wpdb;

        $SearchSerialNumber = sanitize_text_field($Download_serial_number); // Sanitize the input to prevent SQL injection
        $table_name = $wpdb->prefix . 'Warranty_card';

        $query = $wpdb->prepare(
            "SELECT * FROM $table_name WHERE serialnumber = %s AND customer_status = 1",
            $SearchSerialNumber
        );

        $results = $wpdb->get_results($query);
        $customer_table_name = $wpdb->prefix . 'Customer';
        $CutsomerQuery = $wpdb->prepare(
            "SELECT * FROM $customer_table_name WHERE serialnumber = %s",
            $SearchSerialNumber
        );

        $CutsomerResult = $wpdb->get_results($CutsomerQuery);

        if (count($results) != 0) {
            $response = array('status' => 'success', 'message' => $results, 'customer' => $CutsomerResult);
            echo json_encode($response);
        } else {
            $response = array('status' => 'success', 'message' => $results, 'customer' => $CutsomerResult);
            echo json_encode($response);
        }
        // You can send a success response in various ways, such as echoing a JSON response        
    } else {
        // Return an error response if data is missing
        // Again, you can use JSON for consistency
        $response = array('status' => 'error', 'message' => 'Error');
        echo json_encode($response);
    }

    // Make sure to exit after sending the response to prevent further processing
    exit;
}
function register_warranty_callback()
{
    if (isset($_POST['formData'])) {
        // Sanitize and process the form data here
        $customer_name = sanitize_text_field($_POST['formData']['customer_name']);
        $phone_number = sanitize_text_field($_POST['formData']['phone_number']);
        $customer_mail = sanitize_email($_POST['formData']['customer_mail']);
        $serial_number = sanitize_text_field($_POST['formData']['serial_number']);
        $date_of_sale = sanitize_text_field($_POST['formData']['date_of_sale']);
        $customer_location = sanitize_text_field($_POST['formData']['customer_location']);
        $Dealers = sanitize_text_field($_POST['formData']['Dealers']);


        $UniuqeID = uniqid();
        $customer_ID = "MB" . $UniuqeID;
        $current_date_time = date('Y-m-d H:i:s');

        // Perform data storage or processing as needed
        global $wpdb;
        $customer_table_name = $wpdb->prefix . 'customer';

        $data = array(
            'name' => $customer_name,
            'phonenumber' => $phone_number,
            'email' => $customer_mail,
            'serialnumber' => $serial_number,
            'dateofsale' => $date_of_sale,
            'location' => $customer_location,
            'dateofRgister' => $current_date_time,
            'dealer' => $Dealers,
            'customer_ID' => $customer_ID,
        );

        // Insert the data into the table
        $wpdb->insert($customer_table_name, $data);
        if ($wpdb->last_error) {
            echo $wpdb->last_error;
        } else {

            // Send a success response with data

            $new_customer_status = 1;

            // Define the table name with the correct WordPress prefix
            $Wantytable_name = $wpdb->prefix . 'warranty_card';

            // Prepare the data to be updated
            $data = array(
                'customer_id' => $customer_ID,
                'customer_status' => $new_customer_status,
            );

            // Define the WHERE clause to specify which row to update
            $where = array('serialnumber' => $serial_number);

            // Update the data in the table
            $updated = $wpdb->update($Wantytable_name, $data, $where);

            if ($updated !== false) {
                $response_data = array(
                    'customer_name' => $customer_name,
                    'phone_number' => $phone_number,
                    'customer_mail' => $customer_mail,
                    'serial_number' => $serial_number,
                    'date_of_sale' => $date_of_sale,
                    'dateofRgister' => $current_date_time,
                    'customer_location' => $customer_location,
                    'Dealers' => $Dealers,
                    'customer_ID' => $customer_ID,
                );
                // Send JSON response
                wp_send_json_success($response_data);
                // Update was successful
                // echo 'Values updated successfully.';
            } else {
                // Update failed
                // echo 'Error updating values.';
            }
        }
    } else {
        // Send an error response
        wp_send_json_error('Form data not received.');
    }
}

add_action('wp_ajax_register_warranty', 'register_warranty_callback');
add_action('wp_ajax_nopriv_register_warranty', 'register_warranty_callback');




// function handle_update_warranty()
// {
//     global $wpdb;
//     if (isset($_POST['update_warranty'])) {
//         $customer_table_name = $wpdb->prefix . 'Customer';
//         $customer_name = sanitize_text_field($_POST['customer_name']);
//         $phone_number = sanitize_text_field($_POST['phone_number']);
//         $customer_mail = sanitize_text_field($_POST['customer_mail']);
//         $serial_number = sanitize_text_field($_POST['serial_number']); // Use sanitize_email() for email
//         $date_of_sale = sanitize_text_field($_POST['date_of_sale']);
//         $customer_location = sanitize_text_field($_POST['customer_location']);
//         $Dealers = sanitize_text_field($_POST['Dealers']);

//         $UniuqeID = uniqid();
//         $customer_ID = "MB" . $UniuqeID;
//         $current_date_time = date('Y-m-d H:i:s');
//         // $currentDate = "09-09-2023"; // This format gives you the date in "YYYY-MM-DD" format
//         // Prepare data for insertion
//         $data = array(
//             'name' => $customer_name,
//             'phonenumber' => $phone_number,
//             'email' => $customer_mail,
//             'serialnumber' => $serial_number,
//             'dateofsale' => $date_of_sale,
//             'location' => $customer_location,
//             'dateofRgister' => $current_date_time,
//             'dealer' => $Dealers,
//             'customer_ID' => $customer_ID,
//         );

//         // Insert the data into the table
//         $wpdb->insert($customer_table_name, $data);

//         // Check for errors during insertion
//         if ($wpdb->last_error) {
//             return false; // Insertion failed
//         } else {
//         }

//         $new_customer_status = 1;

//         // Define the table name with the correct WordPress prefix
//         $Wantytable_name = $wpdb->prefix . 'warranty_card';

//         // Prepare the data to be updated
//         $data = array(
//             'customer_id' => $customer_ID,
//             'customer_status' => $new_customer_status,
//         );

//         // Define the WHERE clause to specify which row to update
//         $where = array('serialnumber' => $serial_number);

//         // Update the data in the table
//         $updated = $wpdb->update($Wantytable_name, $data, $where);

//         if ($updated !== false) {
//             // Update was successful
//             // echo 'Values updated successfully.';
//         } else {
//             // Update failed
//             // echo 'Error updating values.';
//         }
//         echo '<div id="successfully" class="updated successfully"><p>Warranty updated successfully!</p></div>';
//         echo '<script>document.getElementById("successfully").style.display = "block";</script>';
//     } else {
//         echo '<div class="errorsuccessfully"><p>Serial number not found in the database.</p></div>';
//     }
// }
function raiseComplaint()
{
    global $wpdb;
    if (isset($_POST['raiseComplaint'])) {
        $raiseComplaint_table_name = $wpdb->prefix . 'raisecomplaint';

        $complaintname = sanitize_text_field($_POST['complaintname']);
        $complaintphonenumber = sanitize_text_field($_POST['complaintphonenumber']);
        $complaintemail = sanitize_text_field($_POST['complaintemail']);
        $complaintserialnumber = sanitize_text_field($_POST['complaintserialnumber']); // Use sanitize_email() for email
        $complaintcustomer_ID = sanitize_text_field($_POST['complaintcustomer_ID']);
        // $complaintdateofRgister = sanitize_text_field($_POST['complaintdateofRgister']);
        $complaintdateofsale = sanitize_text_field($_POST['complaintdateofsale']);
        $complaintdealer = sanitize_text_field($_POST['complaintdealer']);
        $complaintlocation = sanitize_text_field($_POST['complaintlocation']);
        $ComplaintList = sanitize_text_field($_POST['ComplaintList']);
        $ComplaintListText = sanitize_text_field($_POST['ComplaintListText']);

        $current_date_time = date('Y-m-d H:i:s');
        function generateTicketNumber()
        {
            $ticketNumber = '';
            $digits = '0123456789';

            for ($i = 0; $i < 16; $i++) {
                $randomIndex = rand(0, strlen($digits) - 1);
                $ticketNumber .= $digits[$randomIndex];
            }

            return $ticketNumber;
        }

        $ticket = generateTicketNumber();
        // $currentDate = "09-09-2023"; // This format gives you the date in "YYYY-MM-DD" format
        // Prepare data for insertion
        $data = array(
            'complaintname' => $complaintname,
            'complaintphonenumber' => $complaintphonenumber,
            'complaintemail' => $complaintemail,
            'complaintserialnumber' => $complaintserialnumber,
            'complaintcustomer_ID' => $complaintcustomer_ID,
            'complaintdateofRgister' => $current_date_time,
            'complaintdateofsale' => $complaintdateofsale,
            'complaintdealer' => $complaintdealer,
            'complaintlocation' => $complaintlocation,
            'complaintList' => $ComplaintList,
            'complaintText' => $ComplaintListText,
            'complaintTicketsNumber' => $ticket,
            'complaintStatus' => 1
        );

        // Insert the data into the table
        $wpdb->insert($raiseComplaint_table_name, $data);

        // Check for errors during insertion
        if ($wpdb->last_error) {
            echo $wpdb->last_error;
        } else {
            echo '<div id="successfully" class="updated successfully"><p>You complaint send successful Your ticker ID ' . $ticket . '</p></div>';
            echo '<script>document.getElementById("successfully").style.display = "block";</script>';
        }
        // $new_customer_status = 1;
        // // Define the table name with the correct WordPress prefix
        // $Wantytable_name = $wpdb->prefix . 'warranty_card';

        // // Prepare the data to be updated
        // $data = array(
        //     'customer_id' => $customer_ID,
        //     'customer_status' => $new_customer_status,
        // );
        // // Define the WHERE clause to specify which row to update
        // $where = array('serialnumber' => $serial_number);

        // // Update the data in the table
        // $updated = $wpdb->update($Wantytable_name, $data, $where);

        // if ($updated !== false) {
        //     // Update was successful
        //     // echo 'Values updated successfully.';
        // } else {
        //     // Update failed
        //     // echo 'Error updating values.';
        // }

    } else {
        echo '<div class="errorsuccessfully"><p>not found in the database.</p></div>';
    }
}
// Add this code to your WordPress plugin or theme's functions.php file

// Define the AJAX action for tracking complaints
add_action('wp_ajax_track_complaint', 'track_complaint');
add_action('wp_ajax_nopriv_track_complaint', 'track_complaint'); // Allow for non-logged-in users

function track_complaint()
{
    // Check if the action is set and is the one we expect
    if (isset($_POST['RaiseTicketNumber'])) {
        $ticketNumber = $_POST['RaiseTicketNumber'];
        global $wpdb;

        $SearchticketNumber = sanitize_text_field($ticketNumber); // Sanitize the input to prevent SQL injection
        $table_name = $wpdb->prefix . 'raisecomplaint';

        $query = $wpdb->prepare(
            "SELECT * FROM $table_name WHERE complaintTicketsNumber = %s",
            $SearchticketNumber
        );
        $results = $wpdb->get_results($query);

        if (count($results) != 0) {
            $response = array('status' => 'success', 'message' => $results);
            echo json_encode($response);
        } else {
            $response = array('status' => 'Fail', 'message' => $query);
            echo json_encode($response);
        }
        // You can send a success response in various ways, such as echoing a JSON response        
    }
    // Always exit to prevent extra output
    wp_die();
}
?>