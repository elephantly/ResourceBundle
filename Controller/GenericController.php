<?php

namespace Elephantly\ResourceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Elephantly\ResourceBundle\Doctrine\ORM\GenericRepositoryInterface;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
* @author purplebabar lalung.alexandre@gmail.com
*/
class GenericController extends Controller
{

    /**
    *   @var GenericRepositoryInterface
    */
    protected $resourceRepository;

    /**
    *   @var string
    */
    protected $class;

    /**
    *   @var string
    */
    protected $name;


    public function __construct(GenericRepositoryInterface $resourceRepository,
                                $class,
                                $name){
        $this->resourceRepository    = $resourceRepository;
        $this->class      = $class;
        $this->name      = $name;
    }

    /**
     * @param int $id
     *
     * @throws \InvalidArgumentException
     *
     * @return JsonResponse
     */
    public function showAction(Request $request, $id)
    {
        $resource = $this->findOr404('find', array($id));

        return $this->render($this->getFromConfig($request, 'template'), array());
    }

    public function indexAction(Request $request)
    {
        // Getting parameters from
        // $limit = $request->query->has('limit') ? $request->query->get('limit') : 20;
        // $offset = $request->query->has('page') ? $request->query->get('page')*$limit : 0;

        // $data = array();

        $resources = $this->findOr404('findBy', array(array()) );

        return $this->render($this->getFromConfig($request, 'template'), array(
            // TODO: pluralize resource name
            $this->name."s" => $resources
        ));

    }

    public function createAction(Request $request)
    {
        $resource = new $this->resourceMetadata();

        $this->resourceRepository->save($resource);

        return $this->render($this->getFromConfig($request, 'template'), array());

    }

    public function updateAction(Request $request, $id)
    {

        $resource = $this->findOr404('find', array($id));

        $this->resourceRepository->save($resource);

        return $this->render($this->getFromConfig($request, 'template'), array());
    }

    public function deleteAction(Request $request, $id)
    {
        $resource = $this->findOr404('find', array($id));

        $this->resourceRepository->delete($resource);

        return $this->render($this->getFromConfig($request, 'template'), array());
    }

    protected function findOr404($repositoryMethod, $arguments = array())
    {
        if (null === $resource = call_user_func_array ( array( $this->resourceRepository, $repositoryMethod), $arguments))
        {
            // throw new NotFoundJsonException();
        }
        return $resource;
    }

    public function getFromConfig(Request $request, $attribute)
    {
        if (!isset($request->attributes->get('_elephantly')[$attribute]))
        {
            throw new InvalidConfigurationException(sprintf('The "%s" parameter has not been found in your configuration', $attribute));
        }
        return $request->attributes->get('_elephantly')[$attribute];
    }

}
