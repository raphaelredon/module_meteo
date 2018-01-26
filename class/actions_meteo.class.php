<?php

/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) 2015 ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file    class/actions_meteo.class.php
 * \ingroup meteo
 * \brief   This file is an example hook overload class file
 *          Put some comments here
 */

/**
 * Class ActionsMeteo
 */
class ActionsMeteo {

    /**
     * @var array Hook results. Propagated to $hookmanager->resArray for later reuse
     */
    public $results = array();

    /**
     * @var string String displayed by executeHook() immediately after return
     */
    public $resprints;

    /**
     * @var array Errors
     */
    public $errors = array();
    
    /**
     * Constructor
     */
    public function __construct() {
    }

    /**
     * Overloading the doActions function : replacing the parent's function with the one below
     *
     * @param   array()         $parameters     Hook metadatas (context, etc...)
     * @param   CommonObject    &$object        The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
     * @param   string          &$action        Current action (if set). Generally create or edit or null
     * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
     * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
     */
    function printTopRightMenu($parameters, &$object, &$action, $hookmanager) {
        echo $this->getMeteo();
    }

    function getMeteo() {
        global $user, $mysoc;
        if($user->town != ""){
            $ville = $user->town;
        } else if($mysoc->town != ""){
            $ville = $mysoc->town;
        } else {
            $ville = "Paris";
        }
        // if vide
//        global $myCompany;
//                var_dump($myCompany->town);
        ini_set("allow_url_fopen", 1);
        $url = "https://query.yahooapis.com/v1/public/yql?q=select%20item.condition%20from%20weather.forecast%20where%20woeid%20in%20(select%20woeid%20from%20geo.places(1)%20where%20text%3D%22" . $ville . "%22)&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys";
        $json = file_get_contents($url);
        $obj = json_decode($json);
        $meteo = $obj->query->results->channel->item->condition;
        $temp = round(($meteo->temp - 32) * 5 / 9);

        return $this->displayMeteo($meteo->code, $meteo->text, $temp);
    }

    function displayMeteo($code, $text, $temp) {
        
        if(file_exists("custom/meteo/img/condition_codes/code".$code.".png")){
            $filepath = "custom/meteo/img/condition_codes/code".$code.".png";
        } else if(file_exists("../custom/meteo/img/condition_codes/code".$code.".png")){
            $filepath = "../custom/meteo/img/condition_codes/code".$code.".png";
        } else {
            $filepath = "../../custom/meteo/img/condition_codes/code".$code.".png";
        }
        $html = '<div style="position: absolute;top: 0;left: -50%;padding-right: 110px;color: white;text-align: center;font-size: 0.9em;"><img style="position: absolute;top: -5px;left: -38px;width: 35px;" src="'.$filepath.'" />'.$text.' <br> '.$temp.'°C</div>';
        return $html;
    }

}
