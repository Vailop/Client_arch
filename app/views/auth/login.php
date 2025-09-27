<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Archandel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="keywords"
        content="Archanděl, desarrollos, Desarrollos, departamentos, Departamentos, casas, Casas, renta, Renta, venta, Venta, residencias, Residencias, oficinas, Oficinas, proyectos inmobiliarios, lujo, CDMX, área metropolitana">
    <meta name="description"
        content="Archanděl Desarrollos Inmobiliarios en CDMX y área metropolitana. Creamos espacios de alto nivel que potencian la calidad de vida, combinando diseño, innovación y exclusividad.">

    <link rel="stylesheet" href="font/iconsmind-s/css/iconsminds.css" />
    <link rel="stylesheet" href="/font/simple-line-icons/css/simple-line-icons.css" />
    <link rel="stylesheet" href="/css/vendor/bootstrap.min.css" />
    <link rel="stylesheet" href="/css/vendor/bootstrap.rtl.only.min.css" />
    <link rel="stylesheet" href="/css/vendor/bootstrap-float-label.min.css" />
    <link rel="stylesheet" href="/css/main.css" />

    <link rel="icon" href="/favicon.png" type="image/x-icon">

</head>

<body class="background show-spinner no-footer">
    <div class="fixed-background"></div>

    <main>
        <div class="container">
            <div class="row h-100">
                <div class="col-12 col-md-10 mx-auto my-auto">
                    <div class="card auth-card">
                        <div class="position-relative image-side">
                            <p class="text-white h6 mb-3 text-justify">Utiliza tus credenciales para iniciar sesión, por
                                favor. Si aún no eres miembro, por favor <a href="#"
                                    class="text-white font-weight-bold">solicita tu regístro.</a>.</p>
                        </div>
                        <div style="background-color: #e9ecef; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                            <strong>Hash de prueba para 'LOVJ850730F96':</strong>
                            <strong>Existen 2 usuarios de prueba SALK921224 su contraseña es alo123</strong>
                            <strong>y LOVJ850730F96 la contraseña es la misma que rfc</strong>

                            <p style="word-wrap: break-word; font-family: monospace;">
                                <?php
                                // Contraseña de prueba.
                                $password_prueba = 'LOVJ850730F96';

                                // Genera el hash de la contraseña de forma segura.
                                // Utiliza password_hash() para una seguridad óptima.
                                $hashed_password = password_hash($password_prueba, PASSWORD_DEFAULT);

                                // Imprime el hash en la pantalla para que lo puedas copiar.
                                echo htmlspecialchars($hashed_password);
                                ?>
                            </p>
                            <p>Copia el hash de arriba y pégalo en la columna `password_hash` de la tabla `usuarios` en
                                tu base de datos.</p>
                        </div>

                        <div class="form-side">
                            <span class="logo-single"></span>

                            <h6 class="mb-4">Iniciar Sesión</h6>
                            <form action="/login" method="post">
                                <label class="form-group has-float-label mb-4">
                                    <input type="text" class="form-control" id="username" name="username" placeholder=""
                                        required />
                                    <span>RFC</span>
                                </label>
                                <label class="form-group has-float-label mb-4">
                                    <input type="password" class="form-control" id="password" name="password"
                                        placeholder="" required />
                                    <span>Contraseña</span>
                                </label>
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="/olvidaste-contrasena">¿Olvidaste tu contraseña?</a>
                                    <button class="btn btn-primary btn-lg btn-shadow" type="submit">ENTRAR</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="/js/vendor/jquery-3.3.1.min.js"></script>
    <script src="/js/vendor/bootstrap.bundle.min.js"></script>
    <script src="/js/dore.script.js"></script>
    <script src="/js/scripts.js"></script>
</body>

</html>