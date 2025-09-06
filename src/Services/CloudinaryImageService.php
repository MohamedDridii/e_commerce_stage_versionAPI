<?php
namespace App\Services;

use Psr\Log\LoggerInterface;
use Cloudinary\Cloudinary;
use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Api\Admin\AdminApi;
use Symfony\component\httpFoundation\File\UploadedFile;

class CloudinaryImageService
{
    private LoggerInterface $logger;
    private Cloudinary $cloudinary;

    public function __construct
    (
        string $cloudName,
        string $apiKey,
        string $apiSecret,
        LoggerInterface $logger,

    )
    {
        //we configure the cloudinary SDK to link to the account in the cloud 
        $this->cloudinary=new Cloudinary([
            'cloud'=>
            [
                'cloud_name'=>$cloudName,//define the cloud name from config/services.yaml
                'api_key'=>$apiKey,//define the apiKey from config/services.yaml
                'api_secret'=>$apiSecret,//define the apiSecret from config/services.yaml
            ],
            'url'=>[
                'secure'=>true //always we use HTTPS
            ]
        ]);
        $this->logger = $logger;
    }
    public function uploiadImage(UploadedFile $file,string $publicId=null,array $options=[]):string
    {
        try
        {
            //validate file
            $this->validateFile($file);

            //prepare upload options
            $uploadOptions =array_merge([
                'folder'=>'products',
                'public_id'=> $publicId ?: 'product_'.uniqid(),//if the public id is provided use it else generate unique id for each image to prevent naming conflicts 
                'overwrite'=> true,//if image with the same public id exists replace it when updateing product images replace old verison
                'resource_type'=>'image',//tells cloudinary this is an image not a raw file 
                'quality'=>'auto',//automatically optimize the image quality by cloudinary 
                'fetch_format'=>'auto',//automatically delivers best format for user's browser 
                'flags'=>'progressive',//create progressive JPEG images 
                'crops'=>'limmit',//Prvents upscalling small images stay small ,large images get resized 
                'width' => 2000,
                'height' => 2000,

            ],$options);//merge the default options with the options string in the params 
             $result=$this->cloudinary->uploadApi()->upload(
                $file->getPathname(),
                $uploadOptions
             );/*$this->cloudinary->uploadApi(): Gets Cloudinary's upload API interface
             ->upload(...): Calls the actual upload method
             $file->getPathname(): Gets temporary file path on server
             $uploadOptions: Passes all our configuration options
             Returns: Array with upload results and image details*/
             $imageUrl=$result['secure_url'];//Extracts the HTTPS URl from upload response $result['secure_url'] cloudinary always provides HTTPS URL in this field 
            
             $this->logger->info('Image uploaded successfully to Cloudinary', [
                'url' => $imageUrl,
                'public_id' => $result['public_id'],
                'original_name' => $file->getClientOriginalName(),
                'size' => $result['bytes'],
                'format' => $result['format']
            ]);
            return $imageUrl;//provides the cloudinary url to calling code,this url gets stored in the data base and returned to frontend 
        }
        catch (\Exception $e) 
        {
            $this->logger->error('Failed to upload image to Cloudinary', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName()
            ]);
            throw new \Exception('Failed to upload image: ' . $e->getMessage());
        }
    }
    
    private function validateFile(UploadedFile $file): void
    {
        // Size check (Cloudinary free tier: max 10MB per image)
        if ($file->getSize() > 10 * 1024 * 1024) {
            throw new \Exception('File size too large. Maximum 10MB allowed.');
        }

        // Type check
        $allowedTypes = [
            'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 
            'image/webp', 'image/bmp', 'image/tiff', 'image/svg+xml'
        ];
        
        if (!in_array($file->getMimeType(), $allowedTypes)) {
            throw new \Exception('Invalid file type. Only images are allowed.');
        }

        // Upload error check
        if ($file->getError() !== UPLOAD_ERR_OK) {
            throw new \Exception('File upload error.');
        }

        // Validate it's actually an image
        if (!getimagesize($file->getPathname())) {
            throw new \Exception('File is not a valid image.');
        }
    }
    
}
