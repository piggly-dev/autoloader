<?php
namespace Piggly;

/**
 * Loads all classes by using their namespace and a custom extension.
 * To able it, this class maintains a log with all directories to the
 * major namespaces prefixes.
 *
 * It works as follows with the directories structure...
 *
 *      ABSPATH
 *          /models
 *              model.php         # Package\Models - The standard model object.
 *              example.model.php # Package\Models - A model object.
 *
 * ...adds the "Package\Models" prefix with the "/models" directory along with
 * the file extensions for each data type.
 *
 * For example, the extensions used are ".controller, .manager or .model". To
 * keep it organized, you have to save the files as:
 *
 *      {filename}.{extension}.php
 *      main.controller.php
 *      database.manager.php
 *      book.model.php
 *
 * When do you have a standard class which can be extended, you have to save
 * as only using the extension name:
 *
 *      {extension}.php
 *      controller.php
 *      manager.php
 *      model.php
 *
 *      ^ These are the standard objects.
 *
 * How to:
 *
 *      Create a new instance to the Autoload;
 *      Add a namespace with addNamespace() function;
 *      Register the autoload with register() function.
 *
 * Keep it in mind:
 *
 *      This class was created to organize the directories and files by a
 *      custom extensions. With you don't agree with this, it's not for you.
 *
 * @author      Caique M Araujo <caique@studiopiggly.com.br>
 * @link        https://github.com/itscaiqueck
 * @author      Piggly <dev@piggly.com.br>
 * @link        https://github.com/piggly-dev
 *
 * @copyright   2018
 * @license     ./LICENSE GNU General Public License v3.0
 * @package     \Piggly
 *
 * @version     1.0.0
 */
class Autoload
{
    /**
     * @var     string The default project namespace.
     * @access  protected
     * @since   1.0.0
     */
    protected $baseNamespace;

    /**
     * @var     string The default directory.
     * @access  protected
     * @since   1.0.0
     */
    protected $baseDir;

    /**
     * @var     string  The absulote path to the project.
     *                  By default takes from this file root.
     * @access  protected
     * @since   1.0.0
     */
    protected $abspath;

    /**
     * @var     array   An associative array where key represents a namespace sufix
     *                  and the value is an array which contains a base directory
     *                  to namespace sufix.
     * @access  protected
     * @since   1.0.0
     */
    protected $prefixes = array ();

    /**
     * Creates the instance, by setting the default app namespace, base directory
     * and absolute path to the project.
     *
     * @param   string                        $baseNamespace      The base namespace.
     * @param   string                        $baseDir            The base directory.
     * @param   string                        $abspath            The absolute directory to project.
     *                                                            By default takes from this file root.
     * @return  \Piggly\Framework\Autoloader                      Itself.
     * @access  public
     * @since   1.0.0
     */
    public function __construct( $baseNamespace, $baseDir = 'src', $abspath = null )
    {
        // Sets
        $this->baseNamespace = $this->parseNamespace( $baseNamespace );
        $this->baseDir       = $this->parseDir( $baseDir );

        // If not sent, then abspath will consider this folder as in the root
        // path to all classes
        if ( is_null ( $abspath ) )
        { $this->abspath = $this->parseDir ( dirname ( dirname( __FILE__ ) ) ); }
        else
        { $this->abspath = $this->parseDir ( $abspath ); }

        return $this;
    }

    /**
     * Registers with spl_autoload_register() function.
     *
     * @return  void
     * @see     spl_autoload_register()
     * @access  public
     * @since   1.0.0
     */
    public function register ()
    { spl_autoload_register( array ( $this, 'loadClass' ) ); }

    /**
     * Links a directory to an namespace prefix.
     *
     * Let's supose you have the following configuration and organization
     * in your project:
     *
     *     Models with the extension *.model.php in the directory /src/models
     *     and namespace as Package\Models.
     *     You will use as following:
     *
     *     $loader = new Autoload( 'Package', 'src' );
     *     $loader->addNamespace( 'Models', 'models', 'model' );
     *
     *     SHORTCUTS
     *
     *     If you set ONLY the namespace, then the base directory and extension
     *     will be equal to $namespace:
     *
     *     $dir = $namespace, $fileExt = $namespace
     *
     *     If you set the namespace and the base directory, then extension
     *     will be equal to $dir:
     *
     *     $fileExt = $dir
     *
     * @param   string $namespace     The namespace prefix.
     *                                E.x.: "Package".
     * @param   string $dir           The base directory to files.
     *                                E.x.: "controllers".
     * @param   string $fileExt       Additional extension to the file.
     *                                E.x.: "controller".
     * @return  void
     * @access  public
     * @since   1.0.0
     */
    public function addNamespace ( $namespace, $dir = null, $fileExt = null )
    {
        // Converts App\Controllers to app/controllers
        if ( is_null ( $dir ) )
        { $dir = str_replace ( '\\', '/', $namespace ); }

        // Converts app/controllers to controllers
        if ( is_null ( $fileExt ) )
        { $fileExt = basename( $dir ); }

        // Normalizing
        $namespace = $this->getNamespace( $namespace );
        $dir       = $this->getDir( $dir );

        // Creates the namespace prefix
        $this->prefixes[$namespace] =
                array
                (
                    'dir' => $dir,
                    'ext' => strtolower ( $fileExt )
                );
    }

    /**
     * Generates all exception namespaces to the parent namespaces.
     *
     * @param   string|array $namespaces        A singular namespace or multiples.
     *                                          You don't have to send the base namespace.
     * @return  void
     * @throws  \InvalidArgumentException       The namespace is not set.
     * @access  public
     * @since   1.0.0
     */
    public function addExceptionClasses ( $namespaces )
    {
        // Converts $namespaces to an array when it's necessary
        $namespaces = is_array ( $namespaces ) ? $namespaces : [ $namespaces ];

        foreach ( $namespaces as $namespace )
        {
            // Creates the parent namespace
            $parent = $this->getNamespace( $namespace );

            // Throws an exception when the parent doesn't exist
            if ( ! isset ( $this->prefixes[$parent] ) )
            { throw new \InvalidArgumentException('The namespace prefix "'.$parent.'" is not set.'); }

            // Sets the new values
            $dir       = $this->prefixes[$parent]['dir'] . 'exceptions' . DIRECTORY_SEPARATOR;
            $namespace = $parent . 'exceptions\\';

            // Initializing prefix array, when needed
            $this->prefixes[$namespace] = array( 'dir' => $dir, 'ext' => 'exception' );
        }
    }

    /**
     * Sets all namespaces into the prefixes array.
     *
     * @return  array    List of all namespaces.
     * @access  public
     * @since   1.0.0
     */
    public function setNamespaces ( $namespaces )
    { $this->prefixes = $namespaces; }

    /**
     * Returns all namespaces set in an array.
     *
     * @return  array    List of all namespaces.
     * @access  public
     * @since   1.0.0
     */
    public function getNamespaces ()
    { return $this->prefixes; }

    /**
     * Loads class from filename.
     *
     * @param   string   $class     The full class name.
     * @return  boolean             TRUE when mapped, FALSE when not.
     * @access  protected
     * @since   1.0.0
     */
    protected function loadClass ( $class )
    {
        // Fixes class name for lowercase
        $class = strtolower ( $class );

        // The actual prefix namespace
        $prefix = $class;

        // Starts to verify from back to front the namespace string
        // in order to find a compatible class.
        while ( false !== $pos = strrpos( $prefix, '\\' ) )
        {
            // Captures prefix till separator bar
            $prefix = substr( $class, 0, $pos + 1 );
            // Gets everything after separator bar
            $relative_class = substr( $class, $pos + 1 );

            // Tries to load the file
            $mapped_file = $this->loadMappedFile ( $prefix, $relative_class );

            // Returns true if has success
            if ( $mapped_file )
            { return true; }

            // Prepares the prefix to continues the interaction
            $prefix = rtrim ( $prefix, '\\' );
        }

        // No file was mapped
        return false;
    }

    /**
     * Finds the file to load it. Does a filter to standard template classes
     * compatible with the extension. For example:
     *
     *      The file "base.controller.php" is a Controller type class.
     *      The file "controller.php" is the standard model to Controllers.
     *
     * Soon, after the controller extension is received, validates as:
     *
     *      "\Package\Controllers\Base" adds the ".controller" extension.
     *      "\Package\Controllers\Controller" doesn't add the ".controller" extension.
     *
     * @param   string   $prefix            The prefix name.
     * @param   string   $relative_class    The class relative name.
     * @return  boolean                     TRUE when found, FALSE when not.
     * @access  protected
     * @since   1.0.0
     */
    protected function loadMappedFile ( $prefix, $relative_class )
    {
        // If there's no directory to prefix, returns false
        if ( ! isset ( $this->prefixes[$prefix] ) )
        { return false; }

        // To each base directory, tries to load the file
        foreach ( $this->prefixes as $prefix )
        {
            // If file is equal to extension, then it's the standard model
            // then, doesn't add the extension
            if ( $relative_class === $prefix['ext'] )
            {
                $file = $prefix['dir']
                        . str_replace( '\\', '/', $relative_class )
                        . '.php';
            }
            else
            {
                $file = $prefix['dir']
                        . str_replace( '\\', '/', $relative_class )
                        . '.' . $prefix['ext']
                        . '.php';
            }

            // Get's the file
            if ( $this->requireFile ( $file ) )
            { return true; }
        }

        // No file was found
        return false;
    }

    /**
     * If the file exists, load it by using require function.
     *
     * @param   string   $file  The file to loads.
     * @return  boolean         TRUE when found, FALSE when not.
     * @see     require()
     * @access  protected
     * @since   1.0.0
     */
    protected function requireFile ( $file )
    {
        if ( file_exists( $file ) )
        {
            require $file;
            return true;
        }

        return false;
    }

    /**
     * Parses the namespace in a valid format.
     *
     * @param   string    $namespace      Namespace to parse.
     * @return  string                   Namespace parsed.
     * @access  protected
     * @since   1.0.0
     */
    protected function parseNamespace ( $namespace )
    { return trim ( strtolower ( $namespace ), '\\' ) . '\\'; }

    /**
     * Parses the directory in a valid format.
     *
     * @param   string    $dir      Diretory to parse.
     * @return  string              Directory parsed.
     * @access  protected
     * @since   1.0.0
     */
    protected function parseDir ( $dir )
    {
        $dir = str_replace ( '\\', DIRECTORY_SEPARATOR, $dir );
        $dir = str_replace ( '/', DIRECTORY_SEPARATOR, $dir );

        return trim ( strtolower ( $dir ), DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;
    }

    /**
     * Gets the namespace by adding the base namespace to it.
     *
     * @param   string   $namespace      Namespace to fill with base namespace.
     * @return  string                   Base namespace with namespace.
     * @access  protected
     * @since   1.0.0
     */
    protected function getNamespace ( $namespace )
    {
        // Normalizing prefix, removing the bars from beginning and ending
        return $this->baseNamespace . $this->parseNamespace( $namespace  );
    }

    /**
     * Gets the directory by adding the base directory to it.
     *
     * @param   string   $dir      Directory to fill with base directory.
     * @return  string             Base directory with directory.
     * @access  protected
     * @since   1.0.0
     */
    protected function getDir ( $dir )
    {
        // Normalizing the base directory, removing the end bar
        return $this->abspath . $this->baseDir . $this->parseDir( $dir  );
    }
}