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
                    <?php echo $modelStatistics; ?>
                </table>
            </div>
        </div>
    </div>

    <hr>

    <div class="container">
        <div class="row">
            <div class="col-12">
                <h2>Textual Analysis</h2>

                <article>
                    <h3>Headings</h3>

                     <table class="table table-bordered table-sm w-fit">
                         <?php echo $headingsTable; ?>
                     </table>
                </article>
            </div>
        </div>
    </div>

    <hr>

    <div class="container">
        <div class="row">
            <div class="col-12">
                <h2>Model Data</h2>

                <article>
                    <h3>Full model</h3>
                    <details>
                        <summary>Click to view the model</summary>

                        <textarea rows="30" cols="80" style="width: 100%; white-space: pre; font-family: monospace;"><?php echo $modelRaw; ?></textarea>

                    </details>
                </article>
                <br>
                <article>
                    <h3>Sections</h3>
                    <details>
                        <summary>Click to view the sections</summary>
                        <?php echo $modelSections; ?>
                    </details>
                </article>
            </div>
        </div>
    </div>
</main>

</body>
</html>
