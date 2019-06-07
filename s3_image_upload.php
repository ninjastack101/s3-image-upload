<?php

// $user_id will be user_id to use in image url
function file_upload(int $user_id)
{
    if($userid){
        $file = $_FILES['image']['tmp_name'];
        if (file_exists($file)) {
            $allowedExts = array("gif", "jpeg", "jpg", "png"); // Specify allowed image types
            $typefile    = explode(".", $_FILES["image"]["name"]);
            $extension   = end($typefile);

            if (!in_array(strtolower($extension), $allowedExts)) {
                //not image
                $data['message'] = "images";
            } else {
                $full_path = "image/" . $user_id . "/profileImg/";
                $path = $_FILES['image']['tmp_name'];
                $image_name = $full_path . preg_replace("/[^a-z0-9\._]+/", "-", strtolower(uniqid() . $_FILES['image']['name']));
                $data['message'] = "sucess";
                $s3_bucket = s3_bucket_upload($path, $image_name);
                if ($s3_bucket['message'] == "sucess") {
                    $data['imagename'] = $s3_bucket['imagepath'];
                    $data['imagepath'] = $s3_bucket['imagename'];
                }
            }
        } else {
            //not a file
            $data['message'] = "images";
        }
    } else {
        //user id not provided
        $data['message'] = "user_id";
    }

    echo json_encode($data);
}

// $temp_path will file temp path
// $image_path will file where to save path

function s3_bucket_upload($temp_path, $image_path)
{
    $bucket = "bucket-name";

    $data = array();

    $data['message'] = "false";

    try {
        $s3Client = new S3Client([
            'version'     => 'latest',
            'region'      => 'us-west-2',
            'credentials' => [
                'key'    => 'aws-key',
                'secret' => 'aws-secretkey',
            ],
        ]);
        $result = $s3Client->putObject([
            'Bucket'     => $bucket,
            'Key'        => $image_path,
            'SourceFile' => $file_Path,
            'ACL'        => 'public-read',
        ]);

        $data['message']   = "sucess";
        $data['imagename'] = $image_path;
        $data['imagepath'] = $result['ObjectURL'];
       

    } catch (Exception $e) {
        $data['message'] = "false";
        // use this line to debug
        // echo $e->getMessage() . "\n"; 
    }

    return $data;
}