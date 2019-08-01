<?php
require_once "vendor/autoload.php"; 
require_once "./random_string.php";

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;
use MicrosoftAzure\Storage\Blob\Models\CreateContainerOptions;
use MicrosoftAzure\Storage\Blob\Models\PublicAccessType;

# Mengatur instance dari Azure::Storage::Client
$connectionString = "DefaultEndpointsProtocol=https;AccountName=sadastest;AccountKey=qbBHow4Qav8nWWjDZWtMoPNF/yPd8Qzz0x2CdROlhkAqJDfMeky3580avPscfzcljXdhrq/UOZkC5C2U0/iM2w==;";
# Membuat blob client.
$blobClient = BlobRestProxy::createBlobService($connectionString);

# Menetapkan metadata dari container
$containerName = 'sadas';

if (isset($_POST['submit'])){
    $fileToUpload = $_FILES['image']['name'];
    $content = fopen($_FILES['image']['tmp_name'],'r');
    $blobClient->createBlockBlob($containerName,$fileToUpload,$content);
}
$listBlobsOptions = new ListBlobsOptions();
$listBlobsOptions->setPrefix("");
?>

<!doctype html>
<html lang="en">
  <head>
    <title>Azure Blob Storage and Computer Vision</title>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  </head>
  <body>
  <script type="text/javascript">
    function processImage() {
        var subscriptionKey = "eff3b5e600314dcca5cc977542c9cbb9";
 
        var uriBase =
            "https://southeastasia.api.cognitive.microsoft.com/vision/v2.0/analyze";
 
        // Request parameters.
        var params = {
            "visualFeatures": "Categories,Description,Color",
            "details": "",
            "language": "en",
        };
 
        // Display the image.
        var sourceImageUrl = document.getElementById("inputImage").value;
        document.querySelector("#sourceImage").src = sourceImageUrl;
 
        // Make the REST API call.
        $.ajax({
            url: uriBase + "?" + $.param(params),
 
            // Request headers.
            beforeSend: function(xhrObj){
                xhrObj.setRequestHeader("Content-Type","application/json");
                xhrObj.setRequestHeader(
                    "Ocp-Apim-Subscription-Key", subscriptionKey);
            },
 
            type: "POST",
 
            // Request body.
            data: '{"url": ' + '"' + sourceImageUrl + '"}',
        })
 
        .done(function(data) {
            // Show formatted JSON on webpage.
            $("#responseTextArea").val(JSON.stringify(data, null, 2));
        })
 
        .fail(function(jqXHR, textStatus, errorThrown) {
            // Display error message.
            var errorString = (errorThrown === "") ? "Error. " :
                errorThrown + " (" + jqXHR.status + "): ";
            errorString += (jqXHR.responseText === "") ? "" :
                jQuery.parseJSON(jqXHR.responseText).message;
            alert(errorString);
        });
    };
</script>
      <div class="container">
          <div class="page-header">
              <h1>Azure Blob Storage and Computer Vision</h1>
          </div>
          <form action="index.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
            <input type="file" class="form-group-control-file border" accept=".jpeg,.jpg,.png" name="image" />
            <button type="submit" name="submit" class="btn btn-primary"> Upload </button>
            </div>
         </form>

         <table class="table table-hover">
                <thead>
                  <tr>
                    <th style="width:30%">Nama File</th>
                    <th style="width:70%">URL</th>
                  </tr>
                </thead>
                <tbody>
                    <?php 
                    do {
                      $result = $blobClient->listBlobs($containerName, $listBlobsOptions);
                        foreach($result->getBlobs() as $blob){
                            ?>
                  <tr>
                    <td><?php echo $blob->getName() ?></td>
                    <td><?php echo $blob->getUrl() ?></td>
                  </tr>
                  <?php 
                }
                $listBlobsOptions->setContinuationToken($result->getContinuationToken());
            }while($result->getContinuationToken());
            ?>
                </tbody>
              </table>
              <br>
              <hr>
              <br>
              <h1>Analyze image:</h1>
              <br>
              Image to analyze:
              <input type="text" name="inputImage" id="inputImage"
                  value="https://upload.wikimedia.org/wikipedia/commons/c/ce/Bill_Gates_in_WEF%2C_2007.jpg" />
              <button class="btn btn-primary" onclick="processImage()">Analyze image</button>
              <br><br>
              <div id="wrapper" style="width:1020px; display:table;">
                  <div id="jsonOutput" style="width:600px; display:table-cell;">
                      Response:
                      <br><br>
                      <textarea id="responseTextArea" class="UIInput"
                                style="width:580px; height:400px;"></textarea>
                  </div>
                  <div id="imageDiv" style="width:420px; display:table-cell;">
                      Source image:
                      <br><br>
                      <img id="sourceImage" width="400" />
                  </div>
              </div>
      </div>

      
      
    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
  </body>
</html>