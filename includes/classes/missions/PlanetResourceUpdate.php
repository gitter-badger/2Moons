<?php

##############################################################################
# *																			 #
# * XG PROYECT																 #
# *  																		 #
# * @copyright Copyright (C) 2008 - 2009 By lucky from Xtreme-gameZ.com.ar	 #
# *																			 #
# *																			 #
# *  This program is free software: you can redistribute it and/or modify    #
# *  it under the terms of the GNU General Public License as published by    #
# *  the Free Software Foundation, either version 3 of the License, or       #
# *  (at your option) any later version.									 #
# *																			 #
# *  This program is distributed in the hope that it will be useful,		 #
# *  but WITHOUT ANY WARRANTY; without even the implied warranty of			 #
# *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the			 #
# *  GNU General Public License for more details.							 #
# *																			 #
##############################################################################

function PlanetResourceUpdate($CurrentUser, $CurrentPlanet, $UpdateTime)
{
	global $ProdGrid, $resource, $reslist, $CONF, $db, $ExtraDM;
	
	if($CurrentUser['urlaubs_modus'] == 1)
		return $CurrentPlanet;
	
	#require_once(ROOT_PATH."/includes/functions/HandleElementBuildingQueue.".PHP_EXT);
	#require_once(ROOT_PATH."/includes/functions/UpdatePlanetBatimentQueueList.".PHP_EXT);

	$CurrentPlanet['metal_max']		= floor(2.5 * pow(1.8331954764,$CurrentPlanet[$resource[22]])) * 5000 * (1 + ($CurrentUser['rpg_stockeur'] * 0.5)) * $CONF['resource_multiplier'] * STORAGE_FACTOR;
	$CurrentPlanet['crystal_max']	= floor(2.5 * pow(1.8331954764,$CurrentPlanet[$resource[23]])) * 5000 * (1 + ($CurrentUser['rpg_stockeur'] * 0.5)) * $CONF['resource_multiplier'] * STORAGE_FACTOR;
	$CurrentPlanet['deuterium_max']	= floor(2.5 * pow(1.8331954764,$CurrentPlanet[$resource[24]])) * 5000 * (1 + ($CurrentUser['rpg_stockeur'] * 0.5)) * $CONF['resource_multiplier'] * STORAGE_FACTOR;

	$MaxMetalStorage                = $CurrentPlanet['metal_max']     * MAX_OVERFLOW;
	$MaxCristalStorage              = $CurrentPlanet['crystal_max']   * MAX_OVERFLOW;
	$MaxDeuteriumStorage            = $CurrentPlanet['deuterium_max'] * MAX_OVERFLOW;
	$ProductionTime               	= ($UpdateTime - $CurrentPlanet['last_update']);
	$CurrentPlanet['last_update'] 	= $UpdateTime;

	if ($CurrentPlanet['planet_type'] == 3|| $CurrentUser['urlaubs_modus'] == 1))
	{
		$CONF['metal_basic_income']     = 0;
		$CONF['crystal_basic_income']   = 0;
		$CONF['deuterium_basic_income'] = 0;
		$CurrentPlanet['metal_perhour']        = 0;
		$CurrentPlanet['crystal_perhour']      = 0;
		$CurrentPlanet['deuterium_perhour']    = 0;
		$CurrentPlanet['energy_used']          = 0;
		$CurrentPlanet['energy_max']           = 0;
	}
	else
	{
		$Caps            	= array();
		$BuildTemp      	= $CurrentPlanet['temp_max'];
		$BuildEnergy		= $CurrentUser[$resource[113]];

		$Caps['metal_perhour'] = $Caps['crystal_perhour'] = $Caps['deuterium_perhour'] = $Caps['energy_used'] = $Caps['energy_max'] = $Caps['deuterium_used'] = 0;

		foreach($reslist['prod'] as $id => $ProdID)
		{
			$BuildLevelFactor = $CurrentPlanet[ $resource[$ProdID]."_porcent" ];
			$BuildLevel = $CurrentPlanet[ $resource[$ProdID] ];
			$Caps['metal_perhour']		+= floor(eval($ProdGrid[$ProdID]['formule']['metal'])     * $CONF['resource_multiplier'] * (1 + (($CurrentUser['rpg_geologue'] * 0.05) + ($CurrentUser['metal_proc_tech'] * 0.02) + ((TIMESTAMP - $CurrentUser[$resource[703]] <= 0) ? ($ExtraDM[703]['add']) : 0))));
			$Caps['crystal_perhour']	+= floor(eval($ProdGrid[$ProdID]['formule']['crystal'])   * $CONF['resource_multiplier'] * (1 + (($CurrentUser['rpg_geologue'] * 0.05) + ($CurrentUser['crystal_proc_tech'] * 0.02) + ((TIMESTAMP - $CurrentUser[$resource[703]] <= 0) ? ($ExtraDM[703]['add']) : 0))));
			if ($ProdID < 4)
			{
				$Caps['deuterium_perhour'] 	+= floor(eval($ProdGrid[$ProdID]['formule']['deuterium']) * $CONF['resource_multiplier'] * (1 + (($CurrentUser['rpg_geologue'] * 0.05) + ($CurrentUser['deuterium_proc_tech'] * 0.02) + ((TIMESTAMP - $CurrentUser[$resource[703]] <= 0) ? ($ExtraDM[703]['add']) : 0))));
				$Caps['energy_used']   		+= floor(eval($ProdGrid[$ProdID]['formule']['energy']) * ($CONF['resource_multiplier']));
			}
			elseif ($ProdID >= 4 )
			{
				$Caps['deuterium_used'] 	+= floor(eval($ProdGrid[$ProdID]['formule']['deuterium']) * ($CONF['resource_multiplier']));
				$Caps['energy_max']			+= floor(eval($ProdGrid[$ProdID]['formule']['energy']) * ($CONF['resource_multiplier']) * (1 + ($CurrentUser['rpg_ingenieur'] * 0.05 )) * ((TIMESTAMP - $CurrentUser[$resource[704]] <= 0) ? (1 + $ExtraDM[704]['add']) : 1));
			}
		}

		if ($Caps['energy_max'] == 0)
		{
			$CurrentPlanet['metal_perhour']     = $CONF['metal_basic_income'];
			$CurrentPlanet['crystal_perhour']   = $CONF['crystal_basic_income'];
			$CurrentPlanet['deuterium_perhour'] = $CONF['deuterium_basic_income'];
			$production_level            = 100;
		}
		elseif ($Caps["energy_max"] >= abs($Caps["energy_used"]))
		{
			$production_level = 100;
		}
		else
		{
			$production_level = floor($Caps['energy_max'] / abs($Caps['energy_used']) * 100);
		}
		if($production_level > 100)
		{
			$production_level = 100;
		}
		elseif ($production_level < 0)
		{
			$production_level = 0;
		}
		
		$CurrentPlanet['metal_perhour']        = $Caps['metal_perhour']* (0.01 * $production_level);
		$CurrentPlanet['crystal_perhour']      = $Caps['crystal_perhour'] * (0.01 * $production_level);
		$CurrentPlanet['deuterium_perhour']    = $Caps['deuterium_perhour'] * (0.01 * $production_level) + $Caps['deuterium_used'];
		$CurrentPlanet['energy_used']          = $Caps['energy_used'];
		$CurrentPlanet['energy_max']           = $Caps['energy_max'];

		if ($CurrentPlanet['metal'] <= $MaxMetalStorage)
		{
			$MetalTheorical  = $CurrentPlanet['metal'] + ($ProductionTime * (($CONF['metal_basic_income'] * $CONF['resource_multiplier']) + $CurrentPlanet['metal_perhour']) / 3600);
			$CurrentPlanet['metal']  = min($MetalTheorical, $MaxMetalStorage);
		}
			
		if ($CurrentPlanet['crystal'] <= $MaxCristalStorage)
		{
			$CristalTheorical  = $CurrentPlanet['crystal'] + ($ProductionTime * (($CONF['crystal_basic_income'] * $CONF['resource_multiplier']) + $CurrentPlanet['crystal_perhour']) / 3600);
			$CurrentPlanet['crystal']  = min($CristalTheorical, $MaxCristalStorage);
		}
		
		if ($CurrentPlanet['deuterium'] <= $MaxDeuteriumStorage)
		{
			$DeuteriumTheorical  = $CurrentPlanet['deuterium'] + ($ProductionTime * (($CONF['deuterium_basic_income'] * $CONF['resource_multiplier']) + $CurrentPlanet['deuterium_perhour']) / 3600);
			$CurrentPlanet['deuterium']  = min($DeuteriumTheorical, $MaxDeuteriumStorage);
		}
	}
	
	$CurrentPlanet['metal']		= max($CurrentPlanet['metal'], 0);
	$CurrentPlanet['crystal']	= max($CurrentPlanet['crystal'], 0);
	$CurrentPlanet['deuterium']	= max($CurrentPlanet['deuterium'], 0);

	if ($Simul == false)
	{
		$QryUpdatePlanet  = "UPDATE ".PLANETS." SET ";
		$QryUpdatePlanet .= "`metal` = '"            . floattostring($CurrentPlanet['metal'], 6)  	."', ";
		$QryUpdatePlanet .= "`crystal` = '"          . floattostring($CurrentPlanet['crystal'], 6)	."', ";
		$QryUpdatePlanet .= "`deuterium` = '"        . floattostring($CurrentPlanet['deuterium'], 6)."', ";
		$QryUpdatePlanet .= "`last_update` = '"      . $CurrentPlanet['last_update']       			."', ";
		$QryUpdatePlanet .= "`last_update` = '"      . $CurrentPlanet['last_update']       			."', ";
		$QryUpdatePlanet .= "`b_hangar_id` = '"      . $CurrentPlanet['b_hangar_id']       			."', ";
		$QryUpdatePlanet .= "`metal_perhour` = '"    . $CurrentPlanet['metal_perhour']     			."', ";
		$QryUpdatePlanet .= "`crystal_perhour` = '"  . $CurrentPlanet['crystal_perhour']   			."', ";
		$QryUpdatePlanet .= "`deuterium_perhour` = '". $CurrentPlanet['deuterium_perhour'] 			."', ";
		$QryUpdatePlanet .= "`metal_max` = '"        . $CurrentPlanet['metal_max']     				."', ";
        $QryUpdatePlanet .= "`crystal_max` = '"      . $CurrentPlanet['crystal_max']   				."', ";
        $QryUpdatePlanet .= "`deuterium_max` = '"    . $CurrentPlanet['deuterium_max'] 				."', ";
		$QryUpdatePlanet .= "`energy_used` = '"      . $CurrentPlanet['energy_used']    			."', ";
		$QryUpdatePlanet .= "`energy_max` = '"       . $CurrentPlanet['energy_max']        			."', ";
		$QryUpdatePlanet .= "`b_hangar` = '". $CurrentPlanet['b_hangar'] ."' ";
		$QryUpdatePlanet .= "WHERE ";
		$QryUpdatePlanet .= "`id` = '". $CurrentPlanet['id'] ."';";
		$db->query($QryUpdatePlanet);
	}
	
	return $CurrentPlanet;
}
?>