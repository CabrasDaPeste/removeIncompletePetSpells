<?php
// ------------------------------
// Script to generate .sql spells 
// ------------------------------

// Habilita todos os erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inicia a classe removeIncompletePetSpells.
class removeIncompletePetSpells {
	// Recebe os dados quando a função for instanciada.
	private $hostname;
	private $dbport;
	private $username;
	private $password;
	private $dbname;
	private $campo;
	
	// Cria a função construtura, que vai receber os dados.
	function __construct($hostname, $dbport, $username, $password, $dbname, $campo) {
		
		// Todas as configurações devem vir abaixo. Não mexer no restante dos arquivos.
		$this->hostname	= $hostname;
		$this->port		= $dbport;
		$this->username	= $username;
		$this->password	= $password;
		$this->dbname	= $dbname;
		$this->campo	= $campo;
	}
	
	// Cria a função privada que: iniciará a conexão do banco de dados.
	private function iniciarConexaoMySQL() {
		
		//Tenta fazer a conexão mysql. Se der certo, irá retornar a conexão realizada.
		try {
			
			$con = new PDO("mysql:host=".$this->hostname.";port=".$this->port.";dbname=".$this->dbname, $this->username, $this->password);
			return $con;
		// Se deu errado, vai jogar um erro na tela.
		} catch (Exception $e) {
			
			// Código para mostrar o erro. TIP: (Copiado da internet. Funciona?)
			die("Oh minha nossa! Houve um erro ao tentar realizar a conexão com o banco de dados.");
		}
	}
	
	// Cria uma função privada que: irá escrever tudo que for passado para ele no arquivo removeIncompletePetSpells.sql
	private function escreveArquivoSQL($spell) {
		
		// Cria ou abre o arquivo removeIncompletePetSpells.sql
		$fp = fopen("removeIncompletePetSpells_".$this->campo.".sql", "a");
 
		// Escreve a query de delete dentro do removeIncompletePetSpells.sql
		$escreve = fwrite($fp, 'UPDATE creature_template_spells SET '.$this->campo.'=0 WHERE '.$this->campo.' = "'.$spell.'";'."\n");
	
		// Fecha o arquivo
		fclose($fp);
	}
	
	// Cria a função privada que: vai pegar o id de todos os feitiços dos pets.
	private function getPetSpells() {
		
		// Chama a função iniciarConexaoMySQL e salva na variável $con
		$con = $this->iniciarConexaoMySQL();
		
		// Tenta fazer uma consulta no banco de dados.
		
		// Prepara a Query dentro da conexão MySQL, e salva-a na variável $rs.
		// TODO: Possível optimização no prepare() para evitar SQL Injection. Mas como é um código de execução única, não preocupado com isso.
		$rs = $con->prepare('SELECT a.'.$this->campo.' AS spell, b.Id AS spell2, b.RecoveryTime AS RecoveryTime, b.CategoryRecoveryTime AS CategoryRecoveryTime, b.CastingTimeIndex AS CastingTimeIndex FROM creature_template_spells a, spell_template b WHERE a.spell1 = b.Id');
			
		// Executa a Query $rs.
		$rs->execute();
		
		// Puxa todos os resultados e salva em uma array
		$result = $rs->fetchAll(PDO::FETCH_ASSOC);
		
		// Cria uma variável global, que vai armazenar a quantidade feitiços deletados.
		$quantidade = 0;
		
		// Executa uma ação pra cada item da array, ou seja, cada spell.
		foreach($result as $spell) {
			
			// Executa a função fazerVerificacao
			if ($spell['spell'] > 0 && $spell['RecoveryTime'] == 0 && $spell['CategoryRecoveryTime'] == 0 && $spell['CastingTimeIndex'] == 1) {
				
				$this->escreveArquivoSQL($spell['spell']);
				$quantidade++;
			}
			
		}
		
		return $quantidade;
		
	}
	
	// Executa exatamente o que eu quero.
	public function execute() {
	
		echo "Quantidade de feitiços removidos do ".$this->campo." : ".$this->getPetSpells();
	}
}

$salvar = new removeIncompletePetSpells("realm.cabrasdapeste.com.br",3306,"user","pass","tbcmangos","spell1");
$salvar->execute();
