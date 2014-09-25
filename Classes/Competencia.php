<?php
/**
 * Created by PhpStorm.
 * User: Delton
 * Date: 23/09/14
 * Time: 17:19
 */

require_once('config/config.cfg');
require_once('translations/pt_br.php');

class Competencia{

    /**
     * @var object $db_connection The database connection
     */
    private $db_connection            = null;
    /**
     * @var bool estado do sucesso do registro de nova disciplina
     */
    public  $registration_successful  = false;
    /**
     * @var array collection of error messages
     */
    public  $errors                   = array();
    /**
     * @var array collection of success / neutral messages
     */
    public  $messages                 = array();
    /**
     * @var int $idProfessor ID do professor que criou competência
     */
    private  $idProfessor           = null;
    /**
     * @var int $idCompetencia ID da competência
     */
    private  $idCompetencia           = null;
    /**
     * @var string $nome nome da competência
     */
    private $nome = "";
    /**
     * @var string $descricaoCompetencia breve descrição da competência
     */
    private $descricaoNome = "";
    /**
     * @var string $atitudeDescricao breve descrição do que seria atitude para essa competência
     */
    private $atitudeDescricao = "";
    /**
     * @var string $habilidadeDescricao breve descrição do que seria atitude para essa competência
     */
    private $habilidadeDescricao = "";
    /**
     * @var string $conhecimentoDescricao breve descrição do que seria conhecimento para essa competência
     */
    private $conhecimentoDescricao = "";
    /**
     * @var boolean $user_is_logged_in Status para verificar se o usuário está logado
     */
    private $user_is_logged_in = false;
    /**
     * the function "__construct()" automatically starts whenever an object of this class is created,
     * you know, when you do "$criarCompetencia = new CriarCompetencia();"
     */
    public function __construct($donothing = null) // Essa construct tá certa, seguir modelo
    {
        if (isset($_POST["registrar_nova_competencia"]) && $donothing == null) {
            // Função para cadastro de nova competência
            $this->criaCompetencia($_POST['nome'],$_POST['descricaoNome'],$_POST['atitudeDescricao'], $_POST['habilidadeDescricao'], $_POST['conhecimentoDescricao'], $_POST['user_id']);
        }
        // Se não estiver cadastrando nova competência, no construct ele retorna valores vazios.
        else{
            $this->idCompetencia = $this->nome = $this->descricaoNome = $this->atitudeDescricao = $this->habilidadeDescricao = $this->conhecimentoDescricao = $this->idProfessor = null;
        }
    }

    /**
     * Função que verifica se a conexão com o BD existe, se nao existir é aberta
     */
    private function databaseConnection(){
        if ($this->db_connection != null) {
            return true;
        } else {
            try {
                $this->db_connection = new PDO('mysql:host='. DB_HOST .';dbname='. DB_NAME . ';charset=utf8', DB_USER, DB_PASS);
                return true;
            } catch (PDOException $e) {
                $this->errors[] = MESSAGE_DATABASE_ERROR;
                print_r($this);
                return false;
            }
        }
    }

    public function getID_byBD($nomeDisciplina = null,$nomeCurso = null){
        if($nomeDisciplina == null || $nomeCurso == null){
            $nomeDisciplina = $this->nomeDisciplina;
            $nomeCurso = $this->nomeCurso;
        }

        $query_get_id_disciplina = $this->db_connection->prepare('SELECT iddisciplina FROM disciplina WHERE nomedisciplina=:nomeDisciplina AND nomecurso=:nomeCurso');
        $query_get_id_disciplina->bindValue(':nomedisciplina', $nomeDisciplina, PDO::PARAM_STR);
        $query_get_id_disciplina->bindValue(':nomecurso', $nomeCurso, PDO::PARAM_STR);
        $query_get_id_disciplina->execute();
        $result = $query_get_id_disciplina->fetchAll();
        if(count($result)>0)
            return $result[0];
        else
            return 0;

    }

    /**
     * Administra toda o sistema de Criação de competência
     * Verifica todos os erros possíveis e cria a competência se ela não existe
     */

    public function criaCompetencia($nome, $descricaoNome, $atitudeDescricao, $habilidadeDescricao, $conhecimentoDescricao, $idProfessor){
        // Remove espaços em branco em excesso das strings
        $nome = trim($nome);
        $descricaoNome = trim($descricaoNome);
        $atitudeDescricao = trim($atitudeDescricao);
        $habilidadeDescricao = trim($habilidadeDescricao);
        $conhecimentoDescricao = trim($conhecimentoDescricao);

        // Atribuição das variáveis ao objeto
        $this->nome = $nome;
        $this->descricaoNome = $descricaoNome;
        $this->atitudeDescricao = $atitudeDescricao;
        $this->habilidadeDescricao = $habilidadeDescricao;
        $this->conhecimentoDescricao = $conhecimentoDescricao;
        $this->idProfessor = $idProfessor;

        //Validação de dados
        if (empty($nome)) {
            $this->errors[] = MESSAGE_NAME_EMPTY;
        } elseif (empty($descricaoNome)){
            $this->errors[] = MESSAGE_DESCRICAO_EMPTY;
        } elseif (empty($atitudeDescricao)){
            $this->errors[] = MESSAGE_DESCRICAO_ATITUDE_EMPTY;
        } elseif (empty($habilidadeDescricao)){
            $this->errors[] = MESSAGE_DESCRICAO_HABILIDADE_EMPTY;
        } elseif (empty($conhecimentoDescricao)){
            $this->errors[] = MESSAGE_DESCRICAO_CONHECIMENTO_EMPTY;
        } elseif (strlen($nome) < 2) {
            $this->errors[] = MESSAGE_NAME_TOO_SHORT;
            //Fim de validações de dados de entrada
            //Inicio das validações de cadastro repitido
        } else if ($this->databaseConnection()) {
            // Verifica se a competência já existe
            // Essa query verifica se possuem nomes idênticos
            $query_check_nome_competencia = $this->db_connection->prepare('SELECT nome FROM competencia WHERE nome=:nome');
            $query_check_nome_competencia->bindValue(':nome', $nome, PDO::PARAM_STR);
            $query_check_nome_competencia->execute();
            $result = $query_check_nome_competencia->fetchAll();
            // Se o nome da competência for encontrada no banco de dados
            if (count($result) > 0) {
                for ($i = 0; $i < count($result); $i++) {
                    $this->errors[] = MESSAGE_COMPETENCIA_ALREADY_EXISTS . $nome;
                }
            } else{
                $stmt = $this->db_connection->prepare("INSERT INTO competencia(nome, descricao_nome, atitude_descricao, habilidade_descricao, conhecimento_descricao, id_professor)  VALUES(:nome, :descricaoNome, :atitudeDescricao, :habilidadeDescricao, :conhecimentoDescricao, :idProfessor)");
                $stmt->bindParam(':nome',$nome, PDO::PARAM_STR);
                $stmt->bindParam(':descricaoNome',$descricaoNome, PDO::PARAM_STR);
                $stmt->bindParam(':atitudeDescricao',$atitudeDescricao, PDO::PARAM_STR);
                $stmt->bindParam(':habilidadeDescricao',$habilidadeDescricao, PDO::PARAM_STR);
                $stmt->bindParam(':conhecimentoDescricao',$conhecimentoDescricao, PDO::PARAM_STR);
                $stmt->bindParam(':idProfessor',$idProfessor, PDO::PARAM_INT);
                $stmt->execute();
                $this->messages[] = WORDING_COMPETENCIA. $nome .WORDING_CREATED_SUCESSFULLY;
            }
        }
    }
    /*
     * Recebe o ID da competência, se ela ainda não tiver sido relacionada para essa disciplina é relacionada utilizando a tabela
     * disciplina_completencia do banco de dados.
     * @return true se a associação funcionou e false se não.
     */
    public function associaCompetencia($idCompetencia){
        if($this->iddisciplina == 0)
            $this->iddisciplina = $this->getID_byBD();
        //Validação de Competência
        if($idCompetencia <= 0){
            $this->errors[] = MESSAGE_COMPETENCIA_DOESNT_EXIST;
            //Validação da disciplina sendo editada
        }else if($this->iddisciplina <= 0){
            $this->errors[] = MESSAGE_DISCIPLINA_DOESNT_EXIST;
        }else{

            //Checa se já existe a relação entre essa disciplina e essa competência, para evitar de duplicar o relacionamento.
            $existeRelacao = false;
            $query_check_disc_comp = $this->db_connection->prepare('SELECT disciplina_iddisciplina FROM disciplina_competencia WHERE disciplina_iddisciplina=:idDisciplina AND competencia_idcompetencia=:idComp');
            $query_check_disc_comp->bindValue(':idDisciplina', $this->iddisciplina, PDO::PARAM_INT);
            $query_check_disc_comp->bindValue(':idComp', $idCompetencia, PDO::PARAM_INT);
            $query_check_disc_comp->execute();
            $result = $query_check_disc_comp->fetchAll();
            if(count($result)>0){
                $existeRelacao = true;
                $this->errors[] = MESSAGE_DISCIPLINA_COMPETENCIA_ALREADY_RELATED;
            }

            if( (! $existeRelacao) && (strlen($this->errors) == 0) ){
                //Associar a competência com a disciplina pelo ID

                $stmt = $this->db_connection->prepare("INSERT INTO disciplina_competencia(disciplina_iddisciplina,competencia_idcompetencia)  VALUES(:idDisc,:idComp )");
                $stmt->bindParam(':idDisc',$this->iddisciplina, PDO::PARAM_INT);
                $stmt->bindParam(':idComp',$idCompetencia, PDO::PARAM_INT);
                $stmt->execute();
                return true;
            }else{
                return false;
            }
        }
    }
    public function getErrors(){
        return $this->errors;
    }

    public function getListaCompetencia(){
        if($this->databaseConnection()){
            $stmt = $this->db_connection->prepare("SELECT nome, idcompetencia FROM competencia");
            //$stmt->bindParam(':nome',, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll();
        }
    }

    public function getArrayOfIDs(){
        if($this->databaseConnection()){
            $stmt = $this->db_connection->prepare("SELECT idcompetencia FROM competencia");
            $stmt->execute();
            $retorno = $stmt->fetchAll();
            return ($retorno);
        }
    }

    public function getArrayOfNames(){
        if($this->databaseConnection()){
            $stmt = $this->db_connection->prepare("SELECT nome FROM competencia");
            $stmt->execute();
            $retorno = $stmt->fetchAll();
            return ($retorno);
        }
    }

    public function associaOA($idOA){

        //TODO MODIFICAR. Está copiado do método associar competência, da classe disciplina.

        if($this->idCompetencia == 0)
            $this->idCompetencia = $this->getID_byBD();
        //Validação de Competência
        if($idOA <= 0){
            $this->errors[] = MESSAGE_COMPETENCIA_DOESNT_EXIST;
            //Validação da disciplina sendo editada
        }else if($this->idCompetencia <= 0){
            $this->errors[] = MESSAGE_DISCIPLINA_DOESNT_EXIST;
        }else{

            //Checa se já existe a relação entre essa disciplina e essa competência, para evitar de duplicar o relacionamento.
            $existeRelacao = false;
            $query_check_disc_comp = $this->db_connection->prepare('SELECT ID FROM competencia_oa WHERE id_competencia=:idCompetencia AND id_OA=:idOA');
            $query_check_disc_comp->bindValue(':idCompetencia', $this->idCompetencia, PDO::PARAM_INT);
            $query_check_disc_comp->bindValue(':idOA', $idOA, PDO::PARAM_INT);
            $query_check_disc_comp->execute();
            $result = $query_check_disc_comp->fetchAll();
            if(count($result)>0){
                $existeRelacao = true;
                $this->errors[] = MESSAGE_DISCIPLINA_COMPETENCIA_ALREADY_RELATED;
            }

            if( (! $existeRelacao) && (strlen($this->errors) == 0) ){
                //Associar a competência com a disciplina pelo ID

                $stmt = $this->db_connection->prepare("INSERT INTO disciplina_competencia(disciplina_iddisciplina,competencia_idcompetencia)  VALUES(:idDisc,:idComp )");
                $stmt->bindParam(':idDisc',$this->idCompetencia, PDO::PARAM_INT);
                $stmt->bindParam(':idComp',$idOA, PDO::PARAM_INT);
                $stmt->execute();
                return true;
            }else{
                return false;
            }
        }

    }
}
//Case de teste
//$competencia = new Competencia();
//$competencia->criaCompetencia('nome','descricao','atitudedesc','habilidadedesc', 'conhhecimentodesc', 1);

?>

