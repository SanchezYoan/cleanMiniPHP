<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-pretitle">
                    Documentation API
                </h2>
                <h2 class="page-title">
                    <?=$title;?>
                </h2>
            </div>

        </div>
    </div>
</div>
<div class="page-body">
    <div class="container-xl">
        <div class="col-12">
            <div class="card pb-5 mb-5">
                <div id="swagger-ui"></div>
                <script src="/assets/node_modules/swagger-ui-dist/swagger-ui-bundle.js"></script>
                <script>
                    window.onload = () => {
                        window.ui = SwaggerUIBundle({
                            url: '<?= $yamlUrl ?? "";?>',
                            dom_id: '#swagger-ui',
                        });
                    };
                </script>
            </div>
        </div>
    </div>
</div>
