symfony-tablefilenameextension
==============================

An extension to create a table from filenames

    // setup a renderer for the filename table
    $renderer = new HTMLRenderer($this->container->get('templating'));
    $renderer->setRoutes(array(
        'new' => 'filename_new',
        'edit' => 'filename_edit',
        'delete' => 'filename_delete',
    ));

    // init the repository
    $queryBuilder = new QueryBuilder('web/uploads/banners');
    $repository = new Repository($queryBuilder);

    // init the bridge and the table
    $bridge = new FilenameBridge($repository);
    $table = new Table($bridge);

    return $this->render(
        'ADifferentBundle:filename:list.html.twig',
        array('table' => $table->createTable($renderer))
    );
