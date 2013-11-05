<?php
namespace Dittto\FilenameExtensionBundle\Model;

/**
 * Class QueryBuilder
 * This is a query builder for filenames
 *
 * @package Dittto\FilenameExtensionBundle\Model
 */
class QueryBuilder
{
    /**
     * The path of the folder to pull the filenames from
     * @var string
     */
    private $path;

    /**
     * The fieldname to order the filenames by
     * @var string
     */
    private $order;

    /**
     * The direction to order the data in, either ASC or DESC
     * @var int
     */
    private $direction;

    /**
     * The number of filenames to discard in the search
     * @var int
     */
    private $offset;

    /**
     * The maximum number of filenames to return
     * @var int
     */
    private $limit;

    /**
     * A list of regex filters to run against the filenames
     * @var array
     */
    private $filters = array();

    /**
     * The class constructor
     *
     * @param string $path The path of the folder to pull the filenames from
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * Adds a regex string to a list of filters. The regex is to filter only filenames that match it
     *
     * @param string $filter The regex filter
     */
    public function addFilter($filter)
    {
        $this->filters[] = $filter;
    }

    /**
     * Stores order options for the query
     *
     * @param string $order The field to order the filenames by
     * @param string $direction The direction to order the filenames by, ASC or DESC
     */
    public function addOrderField($order, $direction = 'ASC')
    {
        // init vars
        $allowedDirections = array('ASC', 'DESC');

        $this->order = $order;
        $this->direction = in_array($direction, $allowedDirections) ? $direction : 'ASC';
    }

    /**
     * Stores the offset of the filenames. The offset is how many filenames we start counting from
     *
     * @param int $offset The offset of the filenames
     */
    public function addOffset($offset)
    {
        $this->offset = $offset;
    }

    /**
     * Stores the maximum number of filenames to return
     *
     * @param int $limit The maximum number of filenames to return
     */
    public function addLimit($limit)
    {
        $this->limit = $limit;
    }

    /**
     * Gets a single filename by it's filename and it's related data. This filename is retrieved after
     * the transforms
     *
     * @param string $name The name of the file to retrieve
     * @return array|null
     */
    public function getOneByName($name)
    {
        // get the filenames
        $files = $this->getFilenames();

        return isset($files[$name]) ? $files[$name] : null;
    }

    /**
     * Gets all filenames, including their related data such as size, modified date, etc
     *
     * @return array
     * @throws Exception Thrown when the order field doesn't exist
     */
    public function getAll()
    {
        // get the filenames and remove any unwanted keys
        $files = array_values($this->getFilenames());

        // split the order facet out
        $facet = array();
        foreach ($files as $key => $file) {
            try {
                $facet[$key] = $file[$this->order];
            } catch(\Exception $e) {
                throw new Exception('Order field '.$this->order.' doesn\'t exist');
            }
        }

        // order the data as requested
        array_multisort($facet, $this->direction === 'ASC' ? SORT_ASC : SORT_DESC, $files);

        // remove files caught by the offset
        array_splice($files, 0, $this->offset);

        // remove all files after the limit
        array_splice($files, $this->limit);

        return $files;
    }

    /**
     * Returns the number of filenames found
     *
     * @return int
     */
    public function getCount()
    {
        return sizeof($this->getFilenames());
    }

    /**
     * Gets the filenames from the folder and applies the transforms and filters
     *
     * @return array
     */
    private function getFilenames()
    {
        // init vars
        $result = array();

        // get the directory handle and dropout if no connection
        $handle = opendir($this->path);
        if (!$handle) {
            return $result;
        }

        // loop through each directory
        while(($filename = readdir($handle)) !== false) {
            // get the file data
            $data = $this->getFileData($this->path, $filename);

            // if the data exists but hasn't already been stored then store it
            if ($data && isset($data['name']) && !isset($result[$data['name']])) {

                // check for the name against the filters
                $match = true;
                foreach ($this->filters as $filter) {
                    if (!preg_match($filter, $data['name'])) {
                        $match = false;
                    }
                }

                // if the name passes through the filters then use it
                if ($match) {
                    $result[$data['name']] = $data;
                }
            }

        }

        return $result;
    }

    /**
     * Gets the data for each filename and returns it as an array
     *
     * @param string $path The path to the file
     * @param string $filename The name of the file
     * @return array
     */
    private function getFileData($path, $filename)
    {
        // init vars
        $data = array();
        $file = $path.DIRECTORY_SEPARATOR.$filename;

        // check the file exists and is actually a file
        if (!file_exists($file) && filetype($file) === 'file') {
            return $data;
        }

        // get the data
        $data['name'] = $this->transformFilename($filename);
        $data['filename'] = $filename;
        $data['size'] = filesize($file);
        $data['createdTime'] = filectime($file);
        $data['updatedTime'] = filemtime($file);

        return $data;
    }

    /**
     * To allow you to group filenames easily, you can perform a transformation on each filename
     * before they're stored. Just extend this class and write the transform function
     *
     * @param string $filename The filename to transform
     * @return string The transformed filename
     */
    protected function transformFilename($filename)
    {
        return $filename;
    }
}