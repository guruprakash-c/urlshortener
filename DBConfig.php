<?php 
namespace App{
	use PDO;
	final class DBConfig{
		private $host = '';
		private $dbname = '';
		private $username = '';
		private $password = '';
		private $tbl_name = 'urls';

		public function Connect(){
			$this->host = 'localhost';
			$this->dbname = '<SOMEDB>';
			$this->username = 'root';
			$this->password = '';
			$pdo = NULL;
			try {
			    $pdo = new PDO("mysql:host=$this->host;dbname=$this->dbname", $this->username, $this->password);
			    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			} catch (PDOException $e) {
			    die("Database connection failed: " . $e->getMessage());
			}
			return $pdo;
		}
		public function GenerateURL($url):mixed{
			$originalUrl = trim(strip_tags($url));
			$urlObj = new UrlDO();
		    if (!filter_var($originalUrl, FILTER_VALIDATE_URL)) {
		    	$urlObj->shortUrl = $urlObj->shortLink = NULL;
		        $urlObj->statusCode = 400;
				$urlObj->statusMessage = 'Invalid URL';
		    }else{
		    	$pdo = self::Connect();
		    	if($pdo){
			    	// Generate a unique short code
				    $shortCode = self::generateShortCode();//ALT: self::generateShortCode($originalUrl);

				    $stmt = $pdo->prepare("SELECT short_code AS Code FROM ".$this->tbl_name." WHERE original_url = :original_url");
				    $stmt->execute(['original_url' => $originalUrl]);
				    /*******************************BY Domain Name ************************** 
				    $originalDomain = str_ireplace('www.', '', parse_url($url, PHP_URL_HOST));
				    $stmt = $pdo->prepare("SELECT short_code AS Code FROM ".$this->tbl_name." WHERE REPLACE(SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(original_url, '/', 3), '://', -1), '/', 1), '?', 1),'www.','') = :original_domain");
				    $stmt->execute(['original_domain' => $originalDomain]);
				    ********************************************/
				    $existingUrl = $stmt->fetch(PDO::FETCH_ASSOC);
				    if(!$existingUrl){
					    // Insert into database
					    $stmt = $pdo->prepare("INSERT INTO ".$this->tbl_name." (original_url, short_code) VALUES (:original_url, :short_code)");
					    if($stmt->execute(['original_url' => $originalUrl, 'short_code' => $shortCode])){
					    	$shortenedUrl = "http://localhost/urlshortener/$shortCode";

					    	$urlObj->shortUrl = $shortenedUrl;
					    	$urlObj->shortLink = '<a href="'.$shortenedUrl.'" target="_blank" rel="external">'.$shortenedUrl.'</a>';
					    	$urlObj->statusCode = 200;
					    	$urlObj->statusMessage = 'OK';
					    }
					}else{
						$urlObj->shortUrl = 'http://localhost/urlshortener/'.$existingUrl['Code'];
						$urlObj->shortLink = NULL;
						$urlObj->statusCode = 100;
					    $urlObj->statusMessage = "\"$originalUrl\" Already exists! ";
					}
				}
		    }
		    return $urlObj;
		}

		public function GetUrl($code):mixed{
			$shortCode = trim(strip_tags($code));
			$pdo = self::Connect();
			$urlObj = new UrlDO();
			if($pdo){
			    // Fetch the original URL from the database
			    $stmt = $pdo->prepare("SELECT original_url AS URI FROM ".$this->tbl_name." WHERE short_code = :short_code");
			    $stmt->execute(['short_code' => $shortCode]);
			    $url = $stmt->fetch(PDO::FETCH_ASSOC);

			    if ($url) {
			        // Redirect to the original URL
			        $urlObj->shortUrl = $url['URI'];
			        $urlObj->statusCode = 200;
				    $urlObj->statusMessage = 'OK';
			    }
			}
			return $urlObj;
		}
		private function generateRandomString($length=6) {
		    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		    $charactersLength = strlen($characters);
		    $shortCode = '';
		    for ($i = 0; $i < $length; $i++) {
		        $shortCode .= $characters[rand(0, $charactersLength - 1)];
		    }
		    return $shortCode;
		}
		private function generateShortCode($originalUrl=NULL, $length = 6) {
			$shortCode = NULL;
			if(!empty($originalUrl)){
			    // Extract keywords from the original URL
			    $keywords = preg_replace('/[^a-zA-Z0-9]/', ' ', $originalUrl);
			    $keywords = explode(' ', $keywords);
			    $keywords = array_filter($keywords);
			    $keywords = array_slice($keywords, 0, 3); // Use the first 3 keywords

			    // Generate a short code based on keywords
			    
			    foreach ($keywords as $keyword) {
			        $shortCode .= substr($keyword, 0, 2); // Use the first 2 letters of each keyword
			    }

			    // If the short code is too short, pad it with random characters
			    if (strlen($shortCode) < $length) {
			        $shortCode .= self::generateRandomString($length - strlen($shortCode));
			    }
			}else{
				$shortCode = self::generateRandomString();
			}
		    return $shortCode;
		}
	}
	final class UrlDO{
		public $shortUrl;
		public $shortLink;
		public $statusCode;
		public $statusMessage;
	}
}