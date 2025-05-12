<?php
require_once '../auth/auth_functions.php';
require_once '../auth/db_connect.php';

if (!is_logged_in()) {
    redirect("../auth/login.php");
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
include './includes/header.php';
?>

<!DOCTYPE html> 
<html lang="en"> 
<head> 
    <meta charset="UTF-8"> 
    <title>Contact Us</title> 
    <style> 
        body { 
            font-family: sans-serif; 
            background: #f4f4f4; 
        } 
        .contact-container { 
            background: #fff; 
            padding: 40px; 
            max-width: 1000px; 
            margin: 40px auto; 
            border-radius: 8px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.1); 
        } 
        h2 { 
            color: #333; 
            font-size: 32px;
            margin-bottom: 20px; 
        } 
        p {
            font-size: 18px; 
            line-height: 1.6;
            margin-bottom: 25px;
        }
        .contact-info {
            padding: 20px;
            background: #f9f9f9;
            border-radius: 6px;
            margin-bottom: 30px;
        }
        .contact-info li { 
            margin-bottom: 15px; 
            font-size: 18px;
        } 
        label { 
            display: block; 
            margin-top: 15px; 
            font-size: 18px; 
            font-weight: bold;
        } 
        input, textarea { 
            width: 100%; 
            padding: 12px; 
            margin-top: 8px; 
            box-sizing: border-box; 
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
        } 
        textarea {
            min-height: 150px;
        }
        button { 
            margin-top: 25px; 
            padding: 15px 25px;
            background: rgb(15, 158, 27); 
            color: white; 
            border: none; 
            cursor: pointer; 
            font-size: 18px;
            border-radius: 4px;
            transition: background 0.3s ease;
        } 
        button:hover { 
            background: #0d8e1a; 
        } 
    </style> 
</head> 
<body> 
    <div class="contact-container"> 
        <h2>Contact Us</h2> 
        <p>Have questions or want to partner with EduShare? We'd love to hear from you.</p> 
        <ul class="contact-info"> 
            <li><strong>Email:</strong> edushare1@gmail.com</li> 
            <li><strong>Phone:</strong> +1 (234) 567-890</li> 
            <li><strong>Address:</strong> 123 Learning Street, Education City, EC 45678</li> 
        </ul>
        <form>
            <label for="message">Your Message</label> 
            <textarea id="message" name="message" rows="6" required placeholder="Please type your message here"></textarea> 
            <button type="submit">Send Message</button> 
        </form>
    </div> 
</body> 
</html>