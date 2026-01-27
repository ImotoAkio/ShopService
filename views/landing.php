<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Service Válvulas | Especialistas desde 1983</title>
    <meta name="description"
        content="Especialistas em Válvulas Redutoras de Pressão e Válvulas de Segurança. Manutenção, Calibração e Venda de Novos. Tradição e Confiança desde 1983.">
    <link rel="stylesheet" href="<?php echo ASSET_URL; ?>/assets/css/landing.css">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

    <!-- Header -->
    <header>
        <div class="container">
            <nav>
                <div class="logo">
                    <img src="<?php echo ASSET_URL; ?>/assets/img/logo.png" alt="Shop Service Logo"
                        style="height: 50px;">
                </div>
                <ul class="nav-links">
                    <li><a href="#services">Serviços</a></li>
                    <li><a href="#about">Sobre</a></li>
                    <li><a href="#contact">Contato</a></li>
                    <!-- Header Contact (Desktop) -->
                    <li>
                        <a href="https://wa.me/5511985452323" target="_blank"
                            style="color: var(--accent-color); font-weight: 600;">
                            <i class="fa-brands fa-whatsapp"></i> (11) 98545-2323
                        </a>
                    </li>
                    <li><a href="<?php echo BASE_URL; ?>/login" class="btn btn-primary"
                            style="padding: 8px 16px;">Login</a></li>
                </ul>
                <a href="<?php echo BASE_URL; ?>/login" class="btn btn-primary mobile-login-btn"
                    style="padding: 8px 16px; display: none;">Login</a>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Excelência em Controle de Fluidos e Segurança Industrial</h1>
            <p>Especialistas absolutos em manutenção, calibração e venda de Válvulas de Segurança e Redutoras de
                Pressão. Sua produção não pode parar.</p>
            <div class="hero-btns">
                <a href="#contact" class="btn btn-primary">Solicitar Orçamento</a>
                <a href="#services" class="btn btn-outline" style="color:white; border-color:white;">Nossos Serviços</a>
            </div>
        </div>
    </section>

    <!-- Authority Bar -->
    <section class="authority-bar">
        <div class="container authority-content">
            <div class="authority-item">
                <span class="authority-number">40+</span>
                <span class="authority-text">Anos de Mercado</span>
            </div>
            <div class="authority-item">
                <span class="authority-number">10k+</span>
                <span class="authority-text">Válvulas Calibradas</span>
            </div>
            <div class="authority-item">
                <span class="authority-number">24h</span>
                <span class="authority-text">Atendimento Emergencial</span>
            </div>
        </div>
    </section>

    <!-- Services -->
    <section id="services" class="services">
        <div class="container">
            <h2 class="section-title">Soluções para Indústria</h2>
            <p class="section-subtitle">Garantimos a eficiência e segurança dos seus equipamentos com tecnologia de
                ponta e equipe certificada.</p>

            <div class="services-grid">
                <!-- Service 1 -->
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fa-solid fa-gear"></i>
                    </div>
                    <h3>Manutenção Especializada</h3>
                    <p>Reparo completo em válvulas de segurança e redutoras de qualquer fabricante. Diagnóstico preciso
                        e recuperação de performance original.</p>
                </div>

                <!-- Service 2 -->
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fa-solid fa-certificate"></i>
                    </div>
                    <h3>Calibração e Testes</h3>
                    <p>Emissão de certificados rastreáveis RBC. Bancadas de teste de última geração para garantir a
                        abertura precisa na pressão de ajuste.</p>
                </div>

                <!-- Service 3 -->
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fa-solid fa-box-open"></i>
                    </div>
                    <h3>Venda de Novos</h3>
                    <p>Revenda técnica de válvulas novas e peças de reposição. Consultoria para dimensionamento correto
                        do equipamento para seu processo.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Catalog Preview -->
    <section class="catalog">
        <div class="container">
            <h2 class="section-title">Equipamentos em Destaque</h2>
            <p class="section-subtitle">Trabalhamos com as melhores marcas do mercado nacional e internacional.</p>

            <div class="catalog-grid">
                <div class="product-card">
                    <i class="fa-solid fa-filter fa-3x" style="color: #cbd5e1;"></i>
                    <h4>Válvulas de Segurança</h4>
                </div>
                <div class="product-card">
                    <i class="fa-solid fa-gauge-high fa-3x" style="color: #cbd5e1;"></i>
                    <h4>Válvulas Redutoras</h4>
                </div>
                <div class="product-card">
                    <i class="fa-solid fa-wrench fa-3x" style="color: #cbd5e1;"></i>
                    <h4>Purgadores de Vapor</h4>
                </div>
                <div class="product-card">
                    <i class="fa-solid fa-temperature-arrow-up fa-3x" style="color: #cbd5e1;"></i>
                    <h4>Válvulas de Controle</h4>
                </div>
            </div>

            <div class="catalog-cta">
                <a href="#contact" class="btn btn-outline">Consultar Estoque</a>
            </div>
        </div>
    </section>

    <!-- About -->
    <section id="about" class="about">
        <div class="container about-content">
            <h2>Tradição e Confiança desde 1983</h2>
            <p>A Shop Service Válvulas nasceu na Mooca, coração industrial de São Paulo, com a missão de manter a
                indústria em movimento. Combinamos décadas de experiência artesanal com processos modernos de qualidade.
                Não somos apenas fornecedores; somos parceiros da sua engenharia.</p>
            <a href="#contact" class="btn btn-primary"
                style="background-color: transparent; border: 1px solid white;">Fale com um Engenheiro</a>
        </div>
    </section>

    <!-- Footer / Contact -->
    <footer id="contact" class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <h4>Shop Service</h4>
                    <div class="contact-info">
                        <p>
                            <a href="https://maps.google.com/?q=Rua+do+Oratório,1437,Mooca,São+Paulo,SP" target="_blank"
                                style="color: inherit; text-decoration: none;">
                                <i class="fa-solid fa-location-dot"></i> Rua do Oratório, 1437<br>
                                <span style="margin-left: 25px;">Mooca, São Paulo - SP</span><br>
                                <span style="margin-left: 25px;">CEP: 03117-000</span>
                            </a>
                        </p>
                        <p><i class="fa-solid fa-phone"></i> (11) 2693-4004 / (11) 2692-5037</p>
                        <p><i class="fa-solid fa-envelope"></i> vendas@shopservicevalvularedutora.com.br</p>
                        <p><i class="fa-solid fa-clock"></i> Seg-Sex: 08:00 - 17:30</p>
                    </div>
                </div>

                <div class="footer-col">
                    <h4>Links Rápidos</h4>
                    <ul class="footer-links">
                        <li><a href="#services">Serviços</a></li>
                        <li><a href="#about">Quem Somos</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/login">Área do Cliente
                                (Login)</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4>Solicite Cotação</h4>
                    <p style="margin-bottom: 1rem; color: #94a3b8;">Resposta rápida para sua urgência.</p>
                    <a href="https://wa.me/5511985452323" class="btn btn-primary"
                        style="width: 100%; text-align: center;">
                        <i class="fa-brands fa-whatsapp"></i> Orçamento via WhatsApp
                    </a>
                </div>
            </div>

            <div class="copyright">
                &copy;
                <?php echo date('Y'); ?> Shop Service Válvulas. Todos os direitos reservados.
            </div>
        </div>
    </footer>

    <!-- Floating WhatsApp Button -->
    <a href="https://wa.me/5511985452323" class="floating-whatsapp" target="_blank">
        <i class="fa-brands fa-whatsapp"></i>
    </a>

</body>

</html>