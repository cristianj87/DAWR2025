<?php
include 'conexion.php';

// Verificar la conexión
if (!$conexion) {
    die("Conexión fallida: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = $_POST["usuario"];
    $password = md5($_POST["password"]);
    $recaptcha_secret = 'aqui la contraseña esecreta recaptcha'; // Reemplaza con tu clave secreta
    $recaptcha_response = $_POST['g-recaptcha-response'];

    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = array(
        'secret' => $recaptcha_secret,
        'response' => $recaptcha_response,
    );

    $options = array(
        'http' => array(
            'method' => 'POST',
            'content' => http_build_query($data),
        ),
    );

    $context = stream_context_create($options);
    $verify = file_get_contents($url, false, $context);
    $captcha_success = json_decode($verify);

    if ($captcha_success->success) {
        // reCAPTCHA verificado, procesa el inicio de sesión
        $query = "SELECT * FROM usuarios WHERE usuario='$usuario' AND pass='$password'";
        $result = mysqli_query($conexion, $query);

        if (mysqli_num_rows($result) > 0) {
            session_start();
            $_SESSION['usuario'] = $usuario;

            echo "<script>
                    alert('¡Información Correcta!');
                    window.location= 'home.php'
                  </script>";
        } else {
            echo "<script>
                    alert('¡Información erronea!');
                    window.location= 'index.html'
                  </script>";
        }
    } else {
        // reCAPTCHA falló
        echo "<script>
                alert('Debes completar el reCAPTCHA. Inténtalo de nuevo.');
                window.location= 'index.html'
              </script>";
    }

    mysqli_close($conexion);
}
?>
