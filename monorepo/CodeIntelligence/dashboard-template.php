<?php

declare(strict_types=1);

/* @var string $modelStatistics */
/* @var string $headingsCount */
/* @var string $headingsTable */
/* @var string $modelRaw */
/* @var string $modelSections */
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HydePHP Code Intelligence Dashboard</title>
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
            <h1>HydePHP Code Intelligence Dashboard</h1>
            <p class="lead">
                This internal monorepo module contains tools to analyse the codebase and
                documentation to improve its quality.
            </p>
        </div>
    </div>
</header>

<main>
    <nav class="container col-12">
        <ul class="nav nav-tabs" id="myTab" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="documentation-tab" data-bs-toggle="tab" data-bs-target="#documentation-tab-pane" type="button" role="tab" aria-controls="documentation-tab-pane" aria-selected="true">Documentation</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="markup-tab" data-bs-toggle="tab" data-bs-target="#markup-tab-pane" type="button" role="tab" aria-controls="markup-tab-pane" aria-selected="false">Markup Analysis</button>
          </li>
        </ul>
    </nav>

    <div class="tab-content">
        <section id="documentation-tab-pane" class="tab-pane fade show active">
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
                        <p class="lead">
                            This can be used to get an overview of all the headings, to ensure a consistent writing style.
                        </p>

                        <article>
                            <h3>Headings <small>(<?php echo $headingsCount; ?>)</small></h3>

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

                        <br>
                        <article class="my-2">
                            <h3>Full model</h3>
                            <details>
                                <summary>Click to view the model</summary>

                                <textarea rows="30" cols="80" style="width: 100%; white-space: pre; font-family: monospace;"><?php echo $modelRaw; ?></textarea>

                            </details>
                        </article>
                        <br>
                        <article class="my-2">
                            <h3>Sections</h3>
                            <details>
                                <summary>Click to view the sections</summary>
                                <?php echo $modelSections; ?>
                            </details>
                        </article>
                    </div>
                </div>
            </div>

            <br>
        </section>
    </div>
</main>

</body>
</html>
