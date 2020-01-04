<?php

namespace wishlist\model;

class Item extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'item';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function liste()
    {
        return $this->belongsTo('wishlist\model\Liste', 'liste_id');
    }

    public function __toString()
    {

        $app = \Slim\Slim::getInstance();
        $rootUri = $app->request->getRootUri();
        $itemUrl = "";
        $img = $this->img;
        if (filter_var($img, FILTER_VALIDATE_URL)) {
            $url = $img;
        }
        else {
            $url = $rootUri . $itemUrl."/img/".$img;

        }


        $modif = "";

        if (isset($_SESSION['session'])) {

            $userID = -1;

            $liste = $this->liste()->first();

            if (!is_null($liste)) {
                $user = $liste->possede()->first();
                if (!is_null($user)) {
                    $userID = $user->user_id;
                }
            }

            if ($_SESSION['session']['user_id'] == $userID) {

                $urlModif = $app->urlFor('route_modifItem', ['id' => $this->id]);
                $modif = "<a href='$urlModif' class='lienSCouleur' id='modifListe'>🖉</a>";
            }
        }

        $str = <<<END
        <div class='item'>
            <div class='itemBg'>
                <img src="$url"></img>
            </div>
            <div class='itemContent'>
            $modif
            <div class='num num_item'> $this->id </div>
                <div class='description'>
END;

        if (is_null($this->liste()->first())) {

            $str .= "Pas de liste pour l'item";
        } else {

            $str .= "<h2>" . $this->liste()->first()['titre'] . "</h2>";
        }

        $str .= "<br>
                    <h4>$this->nom : $this->descr </h4><br>";

        $str .= $this->url != "" ? "<a class='lienSCouleur' href='$this->url' >Infos supplémentaires</a>" : "";

        $str .= "
                    <div class='prix'>$this->tarif</div>
                </div>
                <div class='crop'>
                    <img src='$url'></img> 
                </div>
            </div>
        </div>";

        return $str;
    }
}
