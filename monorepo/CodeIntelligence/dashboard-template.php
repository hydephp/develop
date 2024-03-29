<?php

declare(strict_types=1);

/* @var string $modelStatistics */
/* @var string $headingsCount */
/* @var string $headingsTable */
/* @var string $modelRaw */
/* @var string $modelSections */
/* @var string $markupStatistics */
/* @var string $bladeFiles */
/* @var string $bladeElementIdentifiers */
/* @var string $bladeElementClasses */
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
            <button class="nav-link" id="documentation-tab" data-bs-toggle="tab" data-bs-target="#documentation-tab-pane" type="button" role="tab" aria-controls="documentation-tab-pane" aria-selected="false">Documentation</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="markup-tab" data-bs-toggle="tab" data-bs-target="#markup-tab-pane" type="button" role="tab" aria-controls="markup-tab-pane" aria-selected="false">Markup Analysis</button>
          </li>
        </ul>
    </nav>

    <div class="tab-content mt-4">
        <section id="documentation-tab-pane" class="tab-pane fade">
            <div class="container">
                <div class="row">
                    <header class="col-12">
                        <h2>Statistics</h2>
                        <table class="table table-bordered table-sm w-fit">
                            <caption>Model Statistics</caption>
                            <?php echo $modelStatistics; ?>
                        </table>
                    </header>
                </div>
                <hr>
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
                <hr>
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
            <br>
        </section>

        <section id="markup-tab-pane" class="tab-pane fade">
            <div class="container">
                <div class="row">
                    <header class="col-12">
                        <h2>Markup Statistics</h2>
                        <table class='table table-bordered table-sm w-fit'>
                            <?php echo $markupStatistics; ?>
                        </table>
                    </header>
                </div>
                <div class="row">
                    <h3>Blade Files</h3>
                    <?php echo $bladeFiles; ?>
                </div>
                <br>
                 <div class="row">
                    <h3>Found CSS Identifiers</h3>
                     <div class="ps-2">
                         <?php echo $bladeElementIdentifiers; ?>
                     </div>
                </div>
                <br>
                 <div class="row">
                    <h3>Found CSS Classes</h3>
                     <div class="ps-2">
                         <?php echo $bladeElementClasses; ?>
                     </div>
                </div>
            </div>
        </section>
        <br>
    </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
<script>
    // Remember the last tab selected
    const lastTab = localStorage.getItem('lastTab');

    if (lastTab) {
        const tab = document.getElementById(lastTab);
        if (tab) {
            tab.click();
        }
    } else {
        document.getElementById('documentation-tab').click();
    }

    // Save the last tab selected
    document.querySelectorAll('.nav-link').forEach((tab) => {
        tab.addEventListener('click', (event) => {
            localStorage.setItem('lastTab', event.target.id);
        });
    });
</script>
</body>
</html>
