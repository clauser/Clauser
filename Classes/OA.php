<?php
/**
 * User: Cláuser
 * Date: 02/09/14
 * Time: 09:39
 */
if(class_exists('OA') != true){
class OA {
    private $id;
    private $nome;
    private $descricao;
    private $url;
    private $palavrachave;
    private $idioma;
    private $competencias; //lista de IDs de competencias que a OA tem. IDs separados por ponto-e-vírgula.

        //GETTERS AND SETTERS

    /**
     * @param mixed $descricao
     */
    public function setDescricao($descricao)
    {
        $this->descricao = $descricao;
    }
     /**
     * @return mixed $descricao
     */
    public function getDescricao()
    {
        return $this->descricao;
    }
    /**
     * @param mixed $id
     */
    public function setID($id)
    {
        $this->id = $id;
    }
    /**
     * @return mixed $id
     */
    public function getID()
    {
        return $this->id;
    }
    /**
     * @param mixed $idioma
     */
    public function setIdioma($idioma)
    {
        $this->idioma = $idioma;
    }
     /**
     * @return mixed $idioma
     */
    public function getIdioma()
    {
        return $this->idioma;
    }
     /**
     * @param mixed $nome
     */
    public function setNome($nome)
    {
        $this->nome = $nome;
    }
     /**
     * @return mixed $nome
     */
    public function getNome()
    {
        return $this->nome;
    }
    /**
     * @param mixed $palavrachave
     */
    public function setPalavrachave($palavrachave)
    {
        $this->palavrachave = $palavrachave;
    }
    /**
     * @return mixed $palavrachave
     */
    public function getPalavrachave()
    {
        return $this->palavrachave;
    }
    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }
    /**
     * @return mixed $url
     */
    public function getUrl()
    {
        return $this->url;
    }


    // CONSTRUTOR

    function __construct(){
        $id = 0;
        $nome = $descricao = $url = $palavrachave = $idioma = $competencia = '';
    }

}
}