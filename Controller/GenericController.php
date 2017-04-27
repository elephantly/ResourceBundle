<?php

namespace Elephantly\ResourceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Elephantly\ResourceBundle\Doctrine\ORM\GenericRepositoryInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Elephantly\ResourceBundle\Entity\Actions;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Elephantly\ResourceBundle\Event\ResourceEvent;

// TODO: Index filtering + events + right check

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
    
    /**
    *   @var string
    */
    protected $formType;

    /**
    *   @var s
    */
    protected $eventDispatcher;

    public function __construct(GenericRepositoryInterface $resourceRepository,
                                $class,
                                $name,
                                $formType){
        $this->resourceRepository = $resourceRepository;
        $this->class              = $class;
        $this->name               = $name;
        $this->formType           = $formType;
        $this->eventDispatcher    = new EventDispatcher();
    }

    /**
     * @param int $id
     *
     * @throws \InvalidArgumentException
     *
     * @return JsonResponse
     */
    public function showAction(Request $request, $id = 1)
    {

        $resource = $this->findOr404('find', array($id));

        $this->dispatch(Actions::SHOW, $resource);

        return $this->render($this->getFromConfig($request, 'template'), array(
            $this->name => $resource
        ));
    }

    public function indexAction(Request $request)
    {
        // Getting parameters from
        // $limit = $request->query->has('limit') ? $request->query->get('limit') : 20;
        // $offset = $request->query->has('page') ? $request->query->get('page')*$limit : 0;

        $resources = $this->findOr404('findBy', array(array()) );
        
        $this->dispatch(Actions::INDEX, $resource);

        return $this->render($this->getFromConfig($request, 'template'), array(
            // TODO: pluralize resource name
            $this->name."s" => $resources
        ));

    }

    public function createAction(Request $request)
    {

        $resource = new $this->class();
        $form = $this->createForm($this->formType, $resource, array('data_class' => $this->class));

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $this->resourceRepository->save($resource);

            $this->forward('elephantly.'.$this->name.'.controller:showAction', array('id' => $resource->getId()));
        }

        return $this->render($this->getFromConfig($request, 'template'), array(
            $this->name => $resource,
            'form' => $form->createView()
        ));

    }

    public function updateAction(Request $request, $id)
    {
        $resource = $this->findOr404('find', array($id));
        $form = $this->createForm($this->formType, $resource, array('data_class' => $this->class));

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $this->resourceRepository->save($resource);

        }

        return $this->render($this->getFromConfig($request, 'template'), array(
            $this->name => $resource,
            'form' => $form->createView()
        ));

    }

    public function deleteAction(Request $request, $id)
    {

        $resource = $this->findOr404('find', array($id));

        $this->resourceRepository->delete($resource);

        $this->forward('elephantly.'.$this->name.'.controller:indexAction');

    }

    protected function findOr404($repositoryMethod, $arguments = array())
    {
        if (null === $resource = call_user_func_array ( array( $this->resourceRepository, $repositoryMethod), $arguments))
        {
            throw new NotFoundHttpException();
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

    public function dispatch($action, $resource)
    {
        $event     = new ResourceEvent($resource);
        $eventName = $action.'.'.$this->name;
        $this->eventDispatcher->dispatch($eventName, $event);
    }

}
