<?php namespace components\media; if(!defined('TX')) die('No direct access.');

class Sections extends \dependencies\BaseViews
{

  protected function image()
  {
    
    //Has a direct path been given? (usually by .htaccess)
    if(tx('Data')->get->path->is_set())
    {
      
      //Get the given path.
      $p = tx('Data')->get->path->get();
      
      //Are we getting a cached image?
      if(strpos($p, 'cache/') === 0)
      {
        
        //Create the filename.
        $filename = basename($p);
        
        //Create parameters.
        $resize = Data(array());
        $fit = Data(array());
        $fill = Data(array());
        $crop = Data(array());
        
        //Parse the name for resize parameters.
        $filename = preg_replace_callback('~_resize-(?<width>\d+)-(?<height>\d+)~', function($result)use(&$resize){
          $resize->{0} = $result['width'];
          $resize->{1} = $result['height'];
          return '';
        }, $filename);
        
        //Parse the name for crop parameters.
        $filename = preg_replace_callback('~_crop-(?<x>\d+)-(?<y>\d+)-(?<width>\d+)-(?<height>\d+)~', function($result)use(&$crop){
          $crop->{0} = $result['x'];
          $crop->{1} = $result['y'];
          $crop->{2} = $result['width'];
          $crop->{3} = $result['height'];
          return '';
        }, $filename);
        
        //Use the remaining file name to create a path.
        $path = PATH_COMPONENTS.DS.'media'.DS.'uploads'.DS.'images'.DS.$filename;
        
        //Test if the new path points to an existing file.
        if(!is_file($path)){
          set_status_header(404, sprintf('Image "%s" not found.', $filename));
          exit;
        }
        
      }
      
      //If this is not a cached image, the image just actually does not exist.
      else{
        set_status_header(404, sprintf('Image "%s" not found.', tx('Data')->get->path));
        exit;
      }
      
    }
    
    //No path given. Assume ID has been given and fetch path information from the database.
    else
    {
     
      $image = tx('Sql')->table('media', 'Images')
                ->pk(tx('Data')->get->id)
                ->execute_single();
      
      if($image->is_empty())
        throw new \exception\EmptyResult("Supplied image id was not found. ".tx('Data')->get->id->dump());
      
      $resize = tx('Data')->get->resize->split('/');
      $crop = tx('Data')->get->crop->split('/');
      $path = $image->get_abs_filename();
      
    }
    
    
    return array(
      'download' => tx('Data')->get->download->is_set(),
      'image' => tx('File')->image()
                  ->use_cache(!tx('Data')->get->no_cache->is_set())
                  ->from_file($path)
                  ->allow_growth(tx('Data')->get->allow_growth->is_set())
                  ->allow_shrink(!tx('Data')->get->disallow_shrink->is_set())
                  ->sharpening(!tx('Data')->get->disable_sharpen->is_set())
                  ->resize($resize->{0}, $resize->{1})
                  ->crop($crop->{0}, $crop->{1}, $crop->{2}, $crop->{3})
    );
    
  }

  protected function image_abs()
  {
    
    // throw new \exception\Deprecated();
    
  }

}
