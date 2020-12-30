@extends(config("app.views").'.layouts.app')

@section('content')
<!-- Main Wrapper Start -->
<main id="content" class="main-content-wrapper overflow-hidden">
    <div class="faq-section">
        <div class="container">
            <header class="section-header">
                <h2>FREQUENTLY QUESTIONS</h2>
            </header>
            <article class="section-article">
                <h4>Consulta nuestras preguntas frecuentes para resolver las dudas que tengas</h4>
                <p>Aca puedes encontrar las respuestas para las preguntas mas comunes que recibimos. Estamos actualizandolas constantemente y esperamos
                que puedas encontrar lo que buscas. </p>
            </article>

            <!-- Site Faq -->
            <div class="site-faq-accordion accordion" id="accordionExample">
                <div class="card">
                    <div class="card-header" id="headingOne">
                        <h2 class="mb-0">
                            <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true"
                                    aria-controls="collapseOne">
                                ¿Como puedo inscribir mi negocio en pet world?
                            </button>
                        </h2>
                    </div>

                    <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">
                        <div class="card-body">
                            <p>Haz click en el link que mejor se adapte a los productos o servicios que ofreces, completa el formulario y envia la infirmación. En un plaxo máximo de dos (2) días habiles un asesor se contactará contigo para aclarar dudas, guiarte y acompañarte en el proceso de creacion de tu marca en la plataforma PET WORLD.</p>
                            <p>Servicios veterinarios: (urgencias, consultas, vacunacion, cirugias, examenes..etc): <a href="/a/contact-us/vets">Aqui</a></p>
                            <p>Pet shop (Alimentos, productos y accesorios): <a href="/a/contact-us/shops">Aqui</a></p>
                            <p>Otros (Serv de baño mascotas, colegios, hoteles, guarderias, caminadores, etc): <a href="/a/contact-us/bla">Aqui</a></p>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header" id="headingTwo">
                        <h2 class="mb-0">
                            <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo"
                                    aria-expanded="false" aria-controls="collapseTwo">
                                ¿Por qué estar en Pet World?
                            </button>
                        </h2>
                    </div>
                    <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionExample">
                        <div class="card-body">
                            Nuestra plataforma esta creada especialmente para que tu marca/negocios se digitalece y aumentes tus ventas sin mayor esfuerzo. Ofrece a tus clientes actuales y potenciales la facilidad de comprar productos por tu tienda en linea, con  opcion de entregas a domicilio a nivel local (Bogotá) o nacional, realizar pagos con cualquier metodo de pago digital (TC, PSE, Efectivo) servicio de reservas digitales y evita malas experiancias por tiempos de espera  y capacidad de ateción.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection