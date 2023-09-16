<?php
/**
 * Class to handle game categories
 */

class Category
{
	public $id = null;
	public $name = null;
	public $slug = null;
	public $priority = 0;
	public $description = "";
	public $meta_description = "";
	public $fields = "";

	public function __construct($data = array())
	{
		if (isset($data['id'])) $this->id = (int)$data['id'];
		if (isset($data['name'])) $this->name = $data['name'];
		if (isset($data['description'])) $this->description = $data['description'];
		if (isset($data['meta_description'])) $this->meta_description = $data['meta_description'];
		if (isset($data['fields'])) $this->fields = $data['fields'];
		if (isset($data['priority'])) $this->priority = (int)$data['priority'];
		if ( isset( $data['slug'] ) ) {
			$this->slug = strtolower(str_replace(' ', '-', str_replace('.', '',$data["slug"])));
		} else {
			if ( isset( $data['name'] ) ) $this->slug = strtolower(str_replace(' ', '-', $data["name"]));
		}
		if($this->priority > 10000){
			// Fix possible bug
			$this->priority = 10000;
		}
		if($this->priority < -100){
			// Fix possible bug
			$this->priority = -100;
		}
	}

	public function storeFormValues($params)
	{
		$this->__construct($params);
	}

	public static function getById($id)
	{
		$conn = open_connection();
		$sql = "SELECT * FROM categories WHERE id = :id";
		$st = $conn->prepare($sql);
		$st->bindValue(":id", $id, PDO::PARAM_INT);
		$st->execute();
		$row = $st->fetch();
		if ($row) return new Category($row);
	}

	public static function getBySlug($slug)
	{
		$conn = open_connection();
		$sql = "SELECT * FROM categories WHERE slug = :slug LIMIT 1";
		$st = $conn->prepare($sql);
		$st->bindValue(":slug", $slug, PDO::PARAM_STR);
		$st->execute();
		$row = $st->fetch();
		if ($row) return new Category($row);
	}

	public static function getByName($name)
	{
		$conn = open_connection();
		$sql = "SELECT * FROM categories WHERE name = :name LIMIT 1";
		$st = $conn->prepare($sql);
		$st->bindValue(":name", $name, PDO::PARAM_STR);
		$st->execute();
		$row = $st->fetch();
		if ($row) return new Category($row);
	}

	public static function getIdByName($name)
	{
		$conn = open_connection();
		$sql = "SELECT * FROM categories WHERE name = :name LIMIT 1";
		$st = $conn->prepare($sql);
		$st->bindValue(":name", $name, PDO::PARAM_STR);
		$st->execute();
		$row = $st->fetch();
		if($row){
			return $row['id'];
		} else {
			return null;
		}
	}

	public static function getIdBySlug($slug)
	{
		$conn = open_connection();
		$sql = "SELECT * FROM categories WHERE slug = :slug limit 1";
		$st = $conn->prepare($sql);
		$st->bindValue(":slug", $slug, PDO::PARAM_STR);
		$st->execute();
		$row = $st->fetch();
		if( $row ) {
			return $row['id'];
		} else {
			return null;
		}
	}

	public static function getList($numRows = 1000)
	{
		$conn = open_connection();
		$sql = "SELECT * FROM categories
			ORDER BY priority DESC, name ASC LIMIT :numRows";

		$st = $conn->prepare($sql);
		$st->bindValue(":numRows", $numRows, PDO::PARAM_INT);
		$st->execute();
		$list = array();
		while ($row = $st->fetch())
		{
			$category = new Category($row);
			$list[] = $category;
		}
		$totalRows = $conn->query('SELECT count(*) FROM categories')->fetchColumn();
		return (array(
			"results" => $list,
			"totalRows" => $totalRows
		));
	}

	public static function getCategoryCount($id)
	{
		$conn = open_connection();
		$sql = "SELECT count(*) FROM cat_links WHERE categoryid = :id";
		$st = $conn->prepare($sql);
		$st->bindValue(":id", $id, PDO::PARAM_INT);
		$st->execute();
		$totalRows = $st->fetchColumn();
		return $totalRows;
	}

	public static function getListByCategory($id, int $amount, int $page = 0)
	{
	    $conn = open_connection();
	    // Get only published games.
	    $sql = "SELECT games.id FROM games 
	            JOIN cat_links ON games.id = cat_links.gameid 
	            WHERE cat_links.categoryid = :id AND games.published = 1 
	            ORDER BY cat_links.id DESC LIMIT $amount OFFSET $page";
	    $st = $conn->prepare($sql);
	    $st->bindValue(":id", $id, PDO::PARAM_INT);
	    $st->execute();
	    $row = $st->fetchAll();
	    $list = array();
	    foreach ($row as $item) {
	        $game = new Game;
	        $res = $game->getById($item['id']);
	        array_push($list, $res);
	    }
	    // Count only published games.
	    $sql = "SELECT COUNT(*) FROM games 
	            JOIN cat_links ON games.id = cat_links.gameid 
	            WHERE cat_links.categoryid = :id AND games.published = 1";
	    $st = $conn->prepare($sql);
	    $st->bindValue(":id", $id, PDO::PARAM_INT);
	    $st->execute();
	    $totalRows = $st->fetchColumn();
	    return (array(
	        "results" => $list,
	        "totalRows" => $totalRows,
	        "totalPages" => ceil($totalRows / $amount)
	    ));
	}

	public static function getListByCategories($ids, int $amount, int $page = 0, $random = true){
		$conn = open_connection();
		$random_order = '';
		if ($random) {
			$random_order = ' ORDER BY rand()';
		}
		$sql = "SELECT cl1.* FROM `cat_links` as cl1 ,( SELECT DISTINCT `gameid`,`categoryid` FROM `cat_links`";
		if ($ids) {
			$sql .= " WHERE `categoryid` IN (" . implode(',', $ids) . ")";
		}
		$sql .= $random_order . " LIMIT $amount OFFSET $page ) as cl2 WHERE cl2.gameid = cl1.gameid";
		$st = $conn->prepare($sql);
		$st->execute();
		$rows = $st->fetchAll();
		$list = array();
		$gameIds = [];
		foreach ($rows as $row) {
			if (count($gameIds) > $amount) {
				break;
			}
			if (!in_array($row['gameid'], $gameIds)) {
				$gameIds[] = $row['gameid'];
			}
		}
		foreach ($gameIds as $gameId) {
			if (count($list) < $amount) {
				$game = new Game;
				$res = $game->getById($gameId);
				if ($res && $res->published) {
					array_push($list, $res);
				}
			} else {
				break;
			}
		}
		return array(
			"results" => $list,
			"totalRows" => count($list),
			"totalPages" => 1
		);
	}

	public function addToCategory($gameID, $catID)
	{
		$conn = open_connection();
		$sql = "INSERT INTO cat_links ( gameid, categoryid ) VALUES ( :gameID, :catID )";
		$st = $conn->prepare($sql);
		$st->bindValue(":gameID", $gameID, PDO::PARAM_INT);
		$st->bindValue(":catID", $catID, PDO::PARAM_INT);
		$st->execute();
		$this->id = $conn->lastInsertId();
	}

	public function isCategoryExist($name)
	{
		$conn = open_connection();
		$sql = 'SELECT * FROM categories WHERE name = :name limit 1';
		$st = $conn->prepare($sql);
		$st->bindValue(":name", $name, PDO::PARAM_STR);
		$st->execute();
		$row = $st->fetch();
		if ($row)
		{
			$this->id = $row['id'];
		}
		if ($row)
		{
			return true;
		}
		else
		{
			return false;
		}
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

	public function insert()
	{ 
		if (!is_null($this->id)) trigger_error("Category::insert(): Attempt to insert a Category object that already has its ID property set (to $this->id).", E_USER_ERROR);

		$conn = open_connection();
		$sql = "INSERT INTO categories ( name, slug, description, meta_description ) VALUES ( :name, :slug, :description, :meta_description )";
		$st = $conn->prepare($sql);
		$st->bindValue(":name", $this->name, PDO::PARAM_STR);
		$st->bindValue(":slug", esc_slug($this->slug), PDO::PARAM_STR);
		$st->bindValue(":description", $this->description, PDO::PARAM_STR);
		$st->bindValue(":meta_description", $this->meta_description, PDO::PARAM_STR);
		$st->execute();
		$this->id = $conn->lastInsertId();
	}

	public function update()
	{
		if (is_null($this->id)) trigger_error("Category::update(): Attempt to update a Category object that does not have its ID property set.", E_USER_ERROR);
		//$prev_name = Category::getById($this->id)->name;
		//
		$conn = open_connection();
		$sql = "UPDATE categories SET name=:name, slug=:slug, priority=:priority, description=:description, meta_description=:meta_description, fields=:fields WHERE id = :id";
		$st = $conn->prepare($sql);
		$st->bindValue(":name", $this->name, PDO::PARAM_STR);
		$st->bindValue(":slug", $this->slug, PDO::PARAM_STR);
		$st->bindValue(":description", $this->description, PDO::PARAM_STR);
		$st->bindValue(":meta_description", $this->meta_description, PDO::PARAM_STR);
		$st->bindValue(":fields", $this->fields, PDO::PARAM_STR);
		$st->bindValue(":id", $this->id, PDO::PARAM_INT);
		$st->bindValue(":priority", $this->priority, PDO::PARAM_INT);
		$st->execute();
	}

	public function delete()
	{
		if (is_null($this->id)) trigger_error("Category::delete(): Attempt to delete a Category object that does not have its ID property set.", E_USER_ERROR);

		$conn = open_connection();
		$st = $conn->prepare("DELETE FROM categories WHERE id = :id LIMIT 1");
		$st->bindValue(":id", $this->id, PDO::PARAM_INT);
		$st->execute();
	}

}

?>
