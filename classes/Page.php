<?php

class Page
{
	public $id = null;
	public $createdDate = null;
	public $slug = null;
	public $title = null;
	public $content = null;
	public $fields = '';
	public function __construct( $data=array() ) {
		if ( isset( $data['id'] ) ) $this->id = (int) $data['id'];
		if ( isset( $data['createdDate'] ) ) $this->createdDate = (int) $data['createdDate'];
		if ( isset( $data['title'] ) ) $this->title = htmlspecialchars($data['title']);
		if ( isset( $data['content'] ) ) $this->content = $data['content'];
		if ( isset($data['fields']) ) $this->fields = $data['fields'];
		if ( isset( $data['slug'] ) ) $this->slug = htmlspecialchars(strtolower(str_replace(' ', '-', $data["slug"])));
	}

	public function storeFormValues ( $params ) {

		// Store all the parameters
		$this->__construct( $params );

		// Parse and store the publication date
		if ( isset($params['createdDate']) ) {
			$createdDate = explode ( '-', $params['createdDate'] );

			if ( count($createdDate) == 3 ) {
				list ( $y, $m, $d ) = $createdDate;
				$this->createdDate = mktime ( 0, 0, 0, $m, $d, $y );
			}
		}
	}

	public static function getById( $id ) {
		$conn = open_connection();
		$sql = "SELECT *, UNIX_TIMESTAMP(createdDate) AS createdDate FROM pages WHERE id = :id";
		$st = $conn->prepare( $sql );
		$st->bindValue( ":id", $id, PDO::PARAM_INT );
		$st->execute();
		$row = $st->fetch();
		if ( $row ) return new Page( $row );
	}

	public static function getBySlug( $slug ) {
		$conn = open_connection();
		$sql = "SELECT *, UNIX_TIMESTAMP(createdDate) AS createdDate FROM pages WHERE slug = :slug";
		$st = $conn->prepare( $sql );
		$st->bindValue( ":slug", $slug, PDO::PARAM_STR );
		$st->execute();
		$row = $st->fetch();
		if ( $row ) return new Page( $row );
	}

	public static function getList( $numRows=1000000 ) {
		$conn = open_connection();
		$sql = "SELECT *, UNIX_TIMESTAMP(createdDate) AS createdDate
						FROM pages
						ORDER BY createdDate DESC LIMIT :numRows";

		$st = $conn->prepare( $sql );
		$st->bindValue( ":numRows", $numRows, PDO::PARAM_INT );
		$st->execute();
		$list = array();
		while ( $row = $st->fetch() ) {
			$page = new Page( $row );
			$list[] = $page;
		}
		$totalRows = $conn->query('SELECT count(*) FROM pages')->fetchColumn();
		return ( array ( "results" => $list, "totalRows" => $totalRows ) );
	}

	public function get_fields()
	{
		if($this->fields != ''){
			return json_decode($this->fields, true);
		} else {
			return null;
		}
	}

	public function get_field($key)
	{
		if($this->fields != ''){
			$fields = json_decode($this->fields, true);
			if(isset($fields[$key])){
				return $fields[$key];
			} else {
				return null;
			}
		} else {
			return null;
		}
	}

	public function insert() {
		if ( !is_null( $this->id ) ) trigger_error ( "Page::insert(): Attempt to insert an Page object that already has its ID property set (to $this->id).", E_USER_ERROR );

		$conn = open_connection();
		$sql = "INSERT INTO pages ( createdDate, title, content, slug ) VALUES ( FROM_UNIXTIME(:createdDate), :title, :content, :slug )";
		$st = $conn->prepare ( $sql );
		$st->bindValue( ":createdDate", $this->createdDate, PDO::PARAM_INT );
		$st->bindValue( ":title", $this->title, PDO::PARAM_STR );
		$st->bindValue( ":content", $this->content, PDO::PARAM_STR );
		$st->bindValue( ":slug", esc_slug($this->slug), PDO::PARAM_STR );
		$st->execute();
		$this->id = $conn->lastInsertId();
	}

	public function update() {
		if ( is_null( $this->id ) ) trigger_error ( "Page::update(): Attempt to update an Page object that does not have its ID property set.", E_USER_ERROR );
	 
		$conn = open_connection();
		$sql = "UPDATE pages SET title=:title, content=:content, slug=:slug, fields=:fields WHERE id = :id";
		$st = $conn->prepare ( $sql );
		$st->bindValue( ":title", $this->title, PDO::PARAM_STR );
		$st->bindValue( ":content", $this->content, PDO::PARAM_STR );
		$st->bindValue( ":slug", $this->slug, PDO::PARAM_STR );
		$st->bindValue( ":fields", $this->fields, PDO::PARAM_STR );
		$st->bindValue( ":id", $this->id, PDO::PARAM_INT );
		$st->execute();
	}

	public function delete() {
		if ( is_null( $this->id ) ) trigger_error ( "Page::delete(): Attempt to delete an Page object that does not have its ID property set.", E_USER_ERROR );

		$conn = open_connection();
		$st = $conn->prepare ( "DELETE FROM pages WHERE id = :id LIMIT 1" );
		$st->bindValue( ":id", $this->id, PDO::PARAM_INT );
		$st->execute();
	}

}

?>