<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>PHP Compatibility Checker</title>
    <meta name="author" content="Sarfraz Ahmed">

    <link href="./favicon.ico" rel="icon">

    <link href="./css/bootstrap.min.css" rel="stylesheet">
    <link href="./css/style.css" rel="stylesheet">
</head>

<body>

<main id="main">

    <section id="contact" class="contact">
        <div class="container">

            <div class="section-title">
                <h2>PHP Compatibility Checker</h2>
            </div>


            <div class="row">

                <div class="col-lg-12 mt-5 mt-lg-0 d-flex align-items-stretch">

                    <form action="output.php" target="_blank" method="post" role="form" class="form">
                        <div class="line"></div>

                        <div class="form-content">
                            <div class="row">
                                <div class="form-group col-md-6 mt-3 mt-md-0">
                                    <label for="root">Root Path (Adjust as needed)</label>
                                    <span class="text-danger fw-bold fs-4">*</span>
                                    <input type="text"
                                           class="form-control"
                                           name="root"
                                           id="root"
                                           value="<?= dirname($_SERVER['DOCUMENT_ROOT']) ?>/"
                                           required
                                    >
                                </div>

                                <div class="form-group col-md-6 mt-3 mt-md-0">
                                    <label for="testVersion">Target PHP Version</label>
                                    <span class="text-danger fw-bold fs-4">*</span>
                                    <select class="form-select shadow-none"
                                            name="testVersion"
                                            id="testVersion"
                                            required
                                    >
                                        <option value="" selected>Choose</option>
                                        <option value="7.0">7.0</option>
                                        <option value="7.1">7.1</option>
                                        <option value="7.2">7.2</option>
                                        <option value="7.3">7.3</option>
                                        <option value="7.4">7.4</option>
                                        <option value="8.0">8.0</option>
                                        <option value="8.1">8.1</option>
                                    </select>
                                </div>
                            </div>
                            <br>

                            <div class="row">
                                <div class="form-group col-md-6 mt-3 mt-md-0">
                                    <label for="folders">
                                        Folders to scan separated by comma (Leave empty to scan entire PHP code)
                                    </label>
                                    <input type="text"
                                           class="form-control"
                                           name="folders"
                                           id="folders"
                                    >
                                </div>

                                <div class="form-group col-md-6 mt-3 mt-md-0">
                                    <label for="patterns">(Optional) Patterns to ignore separated by comma</label>
                                    <input type="text"
                                           class="form-control"
                                           name="patterns"
                                           id="patterns"
                                           placeholder="Ex: *.blade*,*tests*"
                                    >
                                </div>
                            </div>
                            <br>

                            <div class="row">
                                <div class="form-group col-md-12 mt-3 mt-md-0">
                                    <label for="excludedSnifs">(Optional) Exclude sniffs separated by new lines</label>
                                    <textarea name="excludedSnifs" id="excludedSnifs" class="form-control" rows="4"></textarea>
                                </div>
                            </div>
                            <br>

                            <div class="row">
                                <div class="form-group col-md-4 mt-3 mt-md-0">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               id="list_files"
                                               name="list_files"
                                               checked
                                        >
                                        <label class="form-check-label" for="list_files">List Processed Files</label>
                                    </div>
                                </div>
                                <div class="form-group col-md-4 mt-3 mt-md-0">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               id="report_code"
                                               name="report_code"
                                               checked
                                        >
                                        <label class="form-check-label" for="report_code">Report Code</label>
                                    </div>
                                </div>
                                <div class="form-group col-md-4 mt-3 mt-md-0">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               id="show_warnings"
                                               name="show_warnings"
                                        >
                                        <label class="form-check-label" for="show_warnings">Show Warnings</label>
                                    </div>
                                </div>
                            </div>

                            <hr>
                            <div class="text-center">
                                <button type="submit" name="submit" class="btn-fancy">Check Now</button>
                            </div>
                        </div>

                        <div class="line"></div>
                    </form>

                </div>

            </div>
        </div>
    </section>

</main>

</body>
</html>
