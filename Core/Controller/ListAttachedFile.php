<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2018-2020 Carlos Garcia Gomez <carlos@facturascripts.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
namespace FacturaScripts\Core\Controller;

use FacturaScripts\Core\Lib\ExtendedController\ListController;
use FacturaScripts\Core\Model\AttachedFile;

/**
 * Controller to list the items in the AttachedFile model
 *
 * @author Carlos García Gómez      <carlos@facturascripts.com>
 * @author Francesc Pineda Segarra  <francesc.pineda.segarra@gmail.com>
 */
class ListAttachedFile extends ListController
{

    /**
     * Returns basic page attributes
     *
     * @return array
     */
    public function getPageData()
    {
        $data = parent::getPageData();
        $data['menu'] = 'admin';
        $data['title'] = 'library';
        $data['icon'] = 'fas fa-book-open';
        return $data;
    }

    /**
     * Load views
     */
    protected function createViews()
    {
        $viewName = 'ListAttachedFile';
        $this->addView($viewName, 'AttachedFile', 'attached-files', 'fas fa-paperclip');
        $this->addSearchFields($viewName, ['filename', 'mimetype']);
        $this->addOrderBy($viewName, ['idfile'], 'code');
        $this->addOrderBy($viewName, ['date', 'hour'], 'date', 2);
        $this->addOrderBy($viewName, ['filename'], 'file-name');
        $this->addOrderBy($viewName, ['size'], 'size');

        /// filters
        $this->addFilterPeriod($viewName, 'date', 'period', 'date');

        $types = $this->codeModel->all('attached_files', 'mimetype', 'mimetype');
        $this->addFilterSelect($viewName, 'mimetype', 'type', 'mimetype', $types);
        $this->addButton('ListAttachedFile',[
            'type' => 'action',
            'action' => 'DescargarZip',
            'color' => 'success',
            'icon' => 'fas fa-file-archive',
            'label' => 'Descargar en zip'
            
            
            
        ]);

        
    }
    
    
     protected function execAfterAction($action)
{
         
                if($action === 'DescargarZip') {
                    
                    // Creamos un instancia de la clase ZipArchive
                    $zip = new \ZipArchive();
 
                    // Creamos y abrimos un archivo zip temporal
                    $zip->open("biblioteca.zip",\ZipArchive::CREATE);

                    //obtenemos los id marcados e instanciamos un nuevo objeto de tipo AttachedFile
                    $codes = $this->request->get('code');
                    $ficheros= new AttachedFile();

                    foreach ($codes as $code) {
                             
                        $fichero= $ficheros->get($code);
                                
                        
                        // Añadimos los ficheros pasándole la ruta y el nombre con que se agregará
                        $zip->addFile($fichero->path,$code."_".$fichero->filename);
    
                        }


                    // Una vez añaadido los archivos deseados cerramos el zip.
 
                    $zip->close();
                    // Creamos las cabezeras que forzaran la descarga del archivo como archivo zip.
                    header("Content-type: application/octet-stream");
                    header("Content-disposition: attachment; filename=biblioteca.zip");
                    // leemos el archivo creado
                    readfile('biblioteca.zip');
                    // eliminamos el archivo temporal creado
                    unlink('biblioteca.zip');//Destruye el archivo temporal  
                    
                    
                    
                    
                }


         
         
     }
}
