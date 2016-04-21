<?php

#CHA:xkalou03

function help() {
	echo '--help Viz spolecne zadani vsech uloh.
--input=fileordir Zadany vstupni soubor nebo adresar se zdrojovym kodem v jazyce C.
Predpokladejte, ze soubory budou v kodovani UTF-8. Je-li zadana cesta k adresari, tak jsou
postupne analyzovany vsechny soubory s priponou .h v tomto adresari a jeho podadresarich.
Pokud je zadana primo cesta k souboru (nikoliv k adresari), tak priponu souboru nekontrolujte.
Pokud nebude tento parametr zadan, tak se analyzuji vsechny hlavickove soubory (opet pouze
s priponou .h) z aktualniho adresare a vsech jeho podadresaru.
--output=filename Zadany vystupni soubor ve formatu XML v kodovani UTF-8 (presny
format viz nize). Pokud tento parametr neni zadan, tak dojde k vypsani vysledku na standardni
vystup.
--pretty-xml=k Skript zformatuje vysledny XML dokument tak, ze (1) kazde nove zanoreni
bude odsazeno o k mezer oproti predchozimu a (2) XML hlavicka bude od korenoveho elementu
oddelena znakem noveho radku. Pokud k neni zadano, tak se pouzije hodnota 4. Pokud tento
parametr nebyl zadan, tak se neodsazuje (ani XML hlavicka od korenoveho elementu).
--no-inline Skript preskoci funkce deklarovane se specifikatorem inline.
--max-par=n Skript bude brat v uvahu pouze funkce, ktere maji n ci mene parametru (n musi
byt vzdy zadano). U funkci, ktere maji promenny pocet parametru, pocitejte pouze s fixnimi
parametry.
--no-duplicates Pokud se v souboru vyskytne vice funkci se stejnym jmenem (napr.
deklarace funkce a pozdeji jeji definice), tak se do vysledneho XML souboru ulozi pouze prvni
z nich (uvazujte pruchod souborem shora dolu). Pokud tento parametr neni zadan, tak se do
vysledneho XML souboru ulozi vsechny vyskyty funkce se stejnym jmenem.
--remove-whitespace Pri pouziti tohoto parametru skript v obsahu atributu rettype a
type (viz nize) nahradi vsechny vyskyty jinych bilych znaku, nez je mezera (tabelator, novy
radek atd.), mezerou a odstrani z nich vsechny prebytecne mezery. Napr. pro funkci „int *
func"(const char arg)" bude hodnota parametru rettype „int*" a hodnota parametru
type pro parametr bude „const char*".';
}

function paramMix($param) {
    
    if (is_array($param) == true) {
        exit(1);
    } else {
        return $param;
    }
}

/* Kontrola parametru programu */

$longopt = array(
    "help", "input:", "output:", "pretty-xml::", "no-inline",
    "max-par:", "no-duplicates", "remove-whitespace");

$options = getopt(NULL , $longopt);
$inputBool =  $outputBool = $maxParBool = $prettyBool = $rmWhite = $noDuplic = $noInline = FALSE;
$filePar = 0;

foreach (array_keys($options) as $opt) {

    switch($opt) {
        
        case 'help':									// zavola napovedu, pokud nejsou zadane i jine parametry, potom chyba
            
            if(count($options) > 1) { exit(1); }
            
            help();                                     
            exit(0);
            
        case 'input':                                   // testuje vstupni soubor
            
            $filePar = paramMix($options['input']);		
            if(($temp = fopen($filePar, 'r')) == FALSE) { exit(2); }
            else {fclose($temp);}
            $inputBool = TRUE;
            break;
        
        case 'output':                                  // testuje vystupni soubor
            
            $fileOut = paramMix($options['output']);	
            if(($temp = fopen($fileOut, 'w')) == FALSE) { exit(3); }
            else {fclose($temp);}
            $outputBool = TRUE;
            break;
        
        case 'pretty-xml':                              // kontrola, zda je zadan parametr              
            
           paramMix($options['pretty-xml']);
           $prettyBool = TRUE;
            
            if($options['pretty-xml'] === false) {
                $pretty = 4;
            }
            else {
                $pretty = $options['pretty-xml'];
            }
            break;
        
        case 'no-inline':                               // kontrola, zda je zadan parametr  
            
            paramMix($options['no-inline']);
            $noInline = TRUE;
            break;
                
        case 'max-par':									// kontrola, zda je zadan parametr  
            
            $maxPar = paramMix($options['max-par']);
            $maxParBool = TRUE;
            break;
        
        case 'no-duplicates':                           // kontrola, zda je zadan parametr  
            paramMix($options['no-duplicates']);
            $noDuplic = TRUE;
            break;
        
        case 'remove-whitespace':						// kontrola, zda je zadan parametr  
            paramMix($options['remove-whitespace']);
            $rmWhite = TRUE;
            break;
    } 
}


if(($argc - 1) != count($options)) {
    exit(1);
}

if(!$inputBool) {

	$filePar = dirname(__FILE__);
}

/* Konec kontroly parametru programu */


/* Priprava vystupniho xml */

$exitXML = new XMLWriter();

$exitXML->openMemory();

$exitXML->startDocument('1.0', 'UTF-8');
$exitXML->startElement('functions');

if($prettyBool == TRUE) {                                   // parametr pretty-xml

    $exitXML->setIndentString(str_repeat(' ', $pretty));
    $exitXML->setIndent(TRUE);
} else {
    $exitXML->setIndent(FALSE);
}

/* Otevirani prislusnych souboru .h rekurzivne */


if($filePar != "" && is_dir($filePar) != FALSE) {

	if($inputBool) {
        $exitXML->writeAttribute('dir', $filePar);
	}
	else {
        $exitXML->writeAttribute('dir', './');
	}

    /* Vyuziti knihovny RecursiveDirectoryIterator pro projduti vsech zadanych podslozek a vybrani vsech hlavickovych souboru .h */
	$path = realpath($filePar);
	$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
	$allHeaders = new RegexIterator($objects, '/^.+\.h$/i', RecursiveRegexIterator::GET_MATCH);
}
else {

	$allHeaders[0] = $filePar;
    $exitXML->writeAttribute('dir', '');
    $filePath = $filePar;
}

if(empty($allHeaders)) {

    exit(0);
}

/* Nacteni aktualne prochazeneho souboru */

foreach($allHeaders as $fileIn){

	var_dump($fileIn);

	if(is_string($fileIn)) {
		$fileIn = $fileIn;
	}
	else {
		$fileIn = $fileIn[0];
	}

    if(is_dir($filePar)) {

        $filePath = substr(realpath($fileIn), strlen(realpath($filePar)) + 1);
    }

/* Uprava nacteneho souboru pomoci regularnich vyrazu - upravuji string */


	$strFile = file_get_contents($fileIn);			              // nacteni souboru

	$editFile = preg_replace('/\/\*.*?\*\//s', '', $strFile);	  // odstraneni blokovych komentaru
	$editFile = preg_replace('/\/\/.*\n/', '', $editFile);	      // odstraneni radkovych komentaru
	$editFile = preg_replace('/\n/', ' ', $editFile);
	$editFile = preg_replace('/{(?:(?>[^{}]+)|(?R))*}/sU', ';', $editFile);

	if($rmWhite == TRUE) {                                        // parametr remove-whitespace

		$editFile = preg_replace('/\s+/', ' ', $editFile);
		$editFile = preg_replace('/\s*\*\s*/', '*', $editFile);
	}

	$strFile = preg_replace('/;/', ";\n", $editFile);
	$strFile = trim(preg_replace('/^\n/m', '', $strFile));
	unset($editFile);
	$strIn = explode(PHP_EOL, $strFile);						  // rozdeleni do pole po radcich
    

    /* Regularni vyrazy pro hledani definic funkci */
    
    $x = 0;
    $funcArray = array();              // ukladani nazvu

	for($i = 0; $i < count($strIn); $i++) {

		$row = trim($strIn[$i]);

		unset($parameters);       // vymazani starych dat

    	if($row == "\n") { continue; }
    

        if(preg_match('/^((?:[\w\s]+(?:\**|\s*)*)(?:\s+|\*)+)(\w+)\s*\(+(.*)\)+.*$/Us', $row, $out)) {

    		$outNext = $out[3];

    		if(preg_match('/(inline)/', $out[1]) == 1 && $noInline == TRUE) { continue; }     // parametr no-inline

        	if($outNext == "") {

        		/* Vypis funkce bez parametru */
        		
                $funType = trim($out[1]);
       			$funName = $out[2];
       		 	$param = $outNext;

        	    if(($noDuplic == TRUE) && (in_array($funName, $funcArray) == TRUE)) { continue; }      
                // parametr no-duplicates - pomoci pole funcArray
                else {
                    $exitXML->startElement('function');
                    $exitXML->writeAttribute('file', $filePath);
                    $exitXML->writeAttribute('name', $funName);
                    $exitXML->writeAttribute('varargs', 'no');
                    $exitXML->writeAttribute('rettype', $funType);
                    $exitXML->fullEndElement();

                    $funcArray[$x] = $funName;
                    $x++;
                }
    	    }

            /* Kontrola funkci s parametry */

    	    if($outNext != "" && count($out) > 3) {

				if(($commas = preg_match_all('/,/', $outNext, $temp)) == 0) {

					$commas = 0;
        		}

        		unset($temp);

	        	//if(preg_match_all('/(?:((?:\s*\w+(?:\**|\s*)*)(?:\s+|\*)+)(?:\s*(?:\w+)\s*,?)?)/', $outNext, $params)) {
	        	if(preg_match_all('/([^,]+)/', $outNext, $params)) {

	        		$args = 0;

	        		for($j = 0; $j < count($params[0]); $j++) {

	        			$parameters[$j] = trim($params[0][$j])."\n";
                        //echo $parameters[$j]."\n";
	        			if(trim($parameters[$j]) == "...") { 
	        				
	        				$args = 1; unset($parameters[$j]); 
	        				$commas--; 
	        			}
	        		}

	        		if($maxParBool == TRUE && count($parameters) > $maxPar) { continue; }

	        		//var_dump($parameters);
	        		$funType = trim($out[1]);
       				$funName = $out[2];

                    /* Vypsani funkce i s parametry */

	        		if($commas == (count($parameters)-1)) {

                        if(($noDuplic == TRUE) && (in_array($funName, $funcArray) == TRUE)) { continue; }
                        else {

                            $exitXML->startElement('function');
                            $exitXML->writeAttribute('file', $filePath);
                            $exitXML->writeAttribute('name', $funName);
                            $exitXML->writeAttribute('varargs', $args == 1 ? 'yes' : 'no');
                            $exitXML->writeAttribute('rettype', $funType);
                            $funcArray[$x] = $funName;
                            $x++;
        				}

        				if(count($parameters) == 1 && trim($parameters[0]) == "void") { $exitXML->fullEndElement(); continue; } 
                        // funkce bez parametru, ale s textem void

                        /* Vypis parametru funkce */

        				$k = 0;
        				foreach ($parameters as $value) {
        					
        					$k++;
        					if(preg_match_all('/^((?:\s*\w+(?:\s*\**)*)+)\s*(?:\s*\w+)$/U', $value, $type)) {

                                $exitXML->startElement('param');
                                $exitXML->writeAttribute('number', ($k));
                                $exitXML->writeAttribute('type', trim($type[1][0]));
                                $exitXML->endElement();
        					}
        				}
                        
                        $exitXML->endElement();
        				unset($k);
	        		}
        		}
        	}
    	}
	}
}

$exitXML->fullEndElement();
$exitXML->endDocument();

$obsah = $exitXML->outputMemory();

$obsah = preg_replace('/\/\>/', ' />', $obsah);

if($prettyBool == FALSE) {

    $obsah = preg_replace('/\n/', '', $obsah);
}

$obsah = preg_replace('/&#9;/', "\t", $obsah);

if(!$outputBool) {

	echo $obsah;
} 
else {

	file_put_contents($fileOut, $obsah);
}
$exitXML->flush();

exit(0);

?>