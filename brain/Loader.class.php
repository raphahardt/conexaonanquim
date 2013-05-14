<?php

abstract class Loader {

  /**
   * Container for already imported library paths.
   *
   * @var    array
   * @since  11.1
   */
  protected static $classes = array();

  /**
   * Container for already imported library paths.
   *
   * @var    array
   * @since  11.1
   */
  public static $imported = array();

  /**
   * Method to discover classes of a given type in a given path.
   *
   * @param   string   $classPrefix  The class name prefix to use for discovery.
   * @param   string   $parentPath   Full path to the parent folder for the classes to discover.
   * @param   boolean  $force        True to overwrite the autoload path value for the class if it already exists.
   * @param   boolean  $recurse      Recurse through all child directories as well as the parent path.
   *
   * @return  void
   *
   * @since   11.1
   */
  public static function discover($parentPath, $force = true, $recurse = true) {
    try {
      if ($recurse) {
        $iterator = new RecursiveIteratorIterator(
          new RecursiveDirectoryIterator($parentPath), RecursiveIteratorIterator::SELF_FIRST
        );
      } else {
        $iterator = new DirectoryIterator($parentPath);
      }

      foreach ($iterator as $file) {
        $fileName = $file->getFilename();

        // Only load for php files.
        // Note: DirectoryIterator::getExtension only available PHP >= 5.3.6
        if ($file->isFile() && substr($fileName, strrpos($fileName, '.') + 1) == 'php') {
          // Get the class name and full path for each file.
          $class = strtolower(preg_replace('#\.class.php$#', '', $fileName));

          // Register the class with the autoloader if not already registered or the force flag is set.
          if (empty(self::$classes[$class]) || $force) {
            self::register($class, $file->getPath() . '/' . $fileName);
          }
        }
      }
    } catch (UnexpectedValueException $e) {
      // Exception will be thrown if the path is not a directory. Ignore it.
    }
  }

  /**
   * Method to get the list of registered classes and their respective file paths for the autoloader.
   *
   * @return  array  The array of class => path values for the autoloader.
   *
   * @since   11.1
   */
  public static function getClassList() {
    return self::$classes;
  }

  /**
   * Loads a class from specified directories.
   *
   * @param   string  $key   The class name to look for (dot notation).
   * @param   string  $base  Search this directory for the class.
   *
   * @return  boolean  True on success.
   *
   * @since   11.1
   */
  public static function import($key) {
    // Only import the library if not already attempted.
    if (!isset(self::$imported[$key])) {
      // Setup some variables.
      $success = false;
      $parts = explode('.', $key);
      $class = array_pop($parts);

      // Handle special case for helper classes.
      if ($class == '*') {
        
        try {
          $base = DJCK_BASE . implode(DS, $parts) . DS;
          $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($base), RecursiveIteratorIterator::SELF_FIRST
          );

          foreach ($iterator as $file) {
            $pathname = str_ireplace($base, '', $file->getPathname());
            $parts_path = explode(DS, $pathname);
            array_pop($parts_path);
            $subpath = array_merge($parts, $parts_path);
            
            $path = substr($file->getFilename(), 0, -10);
            
            $subkey = implode('.', $subpath).'.'.$path;

            if ($file->isFile() && strpos($pathname, '.class.php') !== false) {
              $success = (bool) include_once $base. $pathname;
              
              self::$imported[$subkey] = $success;
            }
          }
        } catch (UnexpectedValueException $e) {
          //erro de diretorio, ignora
        }
        
      } else {
        $path = str_replace('.', DS, $key);
        
        if (is_file(DJCK_BASE . $path . '.class.php')) {
          $success = (bool) include_once DJCK_BASE . $path . '.class.php';
        }
      }

      // Add the import key to the memory cache container.
      self::$imported[$key] = $success;
    }

    return self::$imported[$key];
  }

  /**
   * Load the file for a class.
   *
   * @param   string  $class  The class to be loaded.
   *
   * @return  boolean  True on success
   *
   * @since   11.1
   */
  public static function load($class) {
    // Sanitize class name.
    $class = strtolower($class);

    // If the class already exists do nothing.
    if (class_exists($class)) {
      return true;
    }

    // If the class is registered include the file.
    if (isset(self::$classes[$class])) {
      include_once self::$classes[$class];
      return true;
    }

    return false;
  }

  /**
   * Directly register a class to the autoload list.
   *
   * @param   string   $class  The class name to register.
   * @param   string   $path   Full path to the file that holds the class to register.
   * @param   boolean  $force  True to overwrite the autoload path value for the class if it already exists.
   *
   * @return  void
   *
   * @since   11.1
   */
  public static function register($class, $path, $force = true) {
    // Sanitize class name.
    $class = strtolower($class);

    // Only attempt to register the class if the name and file exist.
    if (!empty($class) && is_file($path)) {
      // Register the class with the autoloader if not already registered or the force flag is set.
      if (empty(self::$classes[$class]) || $force) {
        self::$classes[$class] = $path;
      }
    }
  }

  /**
   * Method to setup the autoloaders for the Joomla Platform.  Since the SPL autoloaders are
   * called in a queue we will add our explicit, class-registration based loader first, then
   * fall back on the autoloader based on conventions.  This will allow people to register a
   * class in a specific location and override platform libraries as was previously possible.
   *
   * @return  void
   *
   * @since   11.3
   */
  public static function setup() {
    // Register the autoloader functions.
    spl_autoload_register(array('Loader', 'load'));
    //spl_autoload_register(array('Loader', '_autoload'));
  }

}

function djimport($path) {
  Loader::import($path);
}