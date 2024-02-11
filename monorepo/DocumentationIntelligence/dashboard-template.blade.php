<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HydePHP Documentation Intelligence Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <style>
        .w-fit {
            width: fit-content;
        }
    </style>
</head>
<body>

<header class="container">
    <div class="row">
        <div class="col-12 py-4 text-center">
            <h1>HydePHP Documentation Intelligence Dashboard</h1>
            <p class="lead">
                This internal monorepo module contains tools to analyse the documentation to improve its quality.
            </p>
        </div>
    </div>
</header>

<main>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h2>Statistics</h2>
                <table class="table table-bordered table-sm w-fit">
                    <caption>Model Statistics</caption>
                    {{ $modelStatistics }}
                </table>
            </div>
        </div>
    </div>

    <hr>

    <div class="container">
        <div class="row">
            <div class="col-12">
                <h2>Raw Data</h2>

                <article>
                    {{ $modelSections }}
                </article>
            </div>
        </div>
    </div>
</main>

</body>
</html>
