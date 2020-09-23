<?php 
    /**
     * Crea una imagen y la dibuja en el navegador
     * @author Josué Hernández <josue.hernandez@vlim.com.mx>
     * @access public
     * @version 0.0.1
     */
    class ImageCreateBrowser{

        private $configuration;
        private $key;
        private $encript_type = "aes-256-cbc";
        private $iv;
        private $encript;
        private $decrypt;

        /**
         * Contructor - Recive un array con las rutas a usar
         * @param array $uri - Contiene las rutas a usar
         * @param string $encript_key - Nombre de la llave con la que esta encriptado el nombre de la imagen
         */
        function __construct(array $uri = [], string $encript_key = "onlyreadkey"){
            try{
                $this->configuration = $this->clean_array_uri($uri);
                $this->key = $encript_key;
                $this->iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->encript_type));
            }catch(\Exception $e){
                return (array) ["error" => $e->getMessage()];
            }
        }

        /**
         * Limpia todos los valores de las variable de una lista (array) para que solo sean de 
         * tipo URL y evitemos una colición en nuestras rutas
         * @param array $uri - Contiene la lista a limpiar
         * @return array
         */
        private function clean_array_uri(array $uri = []) : array {
            try{
                $tmp_uri_clean = [];
                foreach($uri as $key => $val){
                    array_push($tmp_uri_clean, [$key => filter_var($val, FILTER_SANITIZE_URL)]);
                }
                return (array) $tmp_uri_clean;
            }catch(\Exception $e){
                return (array) ["error" => $e->getMessage()];
            }
        }

        /**
         * Encriptar un nombre de imagen
         * @param string $name_img
         * @return string
         */
        public function encript_img(string $name = "") : string{
            $encrypted = openssl_encrypt($name, $this->encript_type, $this->key, 0, $this->iv);
            return (string) $encrypted;
        }

        /**
         * Dencriptar una imagen y regresar el valor
         */
        public function decrypt(string $encrypt_name = ""){
            $encrypted = $encrypt_name . ':' . base64_encode($this->iv);
            $parts = explode(':', $encrypted);
            $decrypted = openssl_decrypt($parts[0], $this->encript_type, $this->key, 0, base64_decode($parts[1]));
            $this->decrypt = $decrypted; 
            return $this;
        }

        /**
         * Inicializa una imagen en el namevgador, la imagen se obtiene de la proviedad: $desencrypt
         * y en argumentos se debe obtener la url que se va a usar
         * @param string $indicate_uri - añade el nombre del indice de tu array de urls que deseas usar
         * @return bool
         */
        public function init(string $indicate_uri){
            $file_location = "";
            foreach($this->configuration as $key => $val){
                if(@$val[$indicate_uri] != null){
                    $file_location = $val[$indicate_uri].$this->decrypt;
                    break;
                }
            }
            switch(explode(".", $this->decrypt)[1]){
                case "png":
                    $im = imagecreatefrompng($file_location);
                    header('Content-Type: image/png');
                    imagepng($im);
                    break;
                case "jpeg":
                    $im = imagecreatefromjpeg($file_location);
                    header('Content-Type: image/png');
                    imagejpeg($im);
                    break;
                case "jpg":
                    $im = imagecreatefromjpeg($file_location);
                    header('Content-Type: image/png');
                    imagejpeg($im);
                    break;
            }
            imagedestroy($im);
        }
    }


    $instance = new ImageCreateBrowser([
        "tickets" => "assets/images/",
        "config" => "/js/jspdf"
    ], "josueeschido");
    $crypt = $instance->encript_img("amarillo.png");
    $file = $instance->decrypt($crypt)->init("tickets");