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

// TODO: right check

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
    *   @var EventDispatcher
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

        return $this->render($this->getFromConfig($request, 'template', true), array(
            $this->name => $resource
        ));
    }

    public function indexAction(Request $request)
    {
        // Getting parameters from
        $limit = $this->getFromQuery($request, 'limit') ? $this->getFromQuery($request, 'limit') : 20;
        $offset = $this->getFromQuery($request, 'page') ? $this->getFromQuery($request, 'page')*$limit : 0;
        $orderBy = $this->getFromQuery($request, 'orderBy') ? $this->getFromQuery($request, 'orderBy') : array();
        $criteria = $this->getFromQuery($request, 'criteria') ? $this->getFromQuery($request, 'criteria') : array();

        // \Doctrine\Common\Util\Debug::dump($criteria);exit;

        $resources = $this->findOr404('findBy', array($criteria, $orderBy, $limit, $offset) );

        $this->dispatch(Actions::INDEX, $resources);

        return $this->render($this->getFromConfig($request, 'template', true), array(
            // TODO: pluralize resource name
            $this->name."s" => $resources
        ));

    }

    public function createAction(Request $request)
    {

        $this->accessGrantedOr403($request, Actions::CREATE);

        $resource = new $this->class();
        $this->dispatch(Actions::PRE_CREATE, $resource);
        $form = $this->createForm($this->formType, $resource, array('data_class' => $this->class));

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $this->resourceRepository->save($resource);

            $this->dispatch(Actions::POST_CREATE, $resource);

            return $this->redirectToRoute($this->getFromConfig($request, 'redirect', true), array('id' => $resource->getId()));
        }

        return $this->render($this->getFromConfig($request, 'template', true), array(
            $this->name => $resource,
            'form' => $form->createView()
        ));

    }

    public function updateAction(Request $request, $id)
    {
        $resource = $this->findOr404('find', array($id));
        $this->dispatch(Actions::PRE_UPDATE, $resource);
        $form = $this->createForm($this->formType, $resource, array('data_class' => $this->class));

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $this->resourceRepository->save($resource);

        }
        $this->dispatch(Actions::POST_UPDATE, $resource);

        return $this->render($this->getFromConfig($request, 'template', true), array(
            $this->name => $resource,
            'form' => $form->createView()
        ));

    }

    public function deleteAction(Request $request, $id)
    {

        $resource = $this->findOr404('find', array($id));

        $this->dispatch(Actions::PRE_DELETE, $resource);
        $this->resourceRepository->delete($resource);
        $this->dispatch(Actions::POST_DELETE, $resource);

        return $this->redirectToRoute($this->getFromConfig($request, 'redirect', true));

    }

    protected function findOr404($repositoryMethod, $arguments = array())
    {
        if (null === $resource = call_user_func_array ( array( $this->resourceRepository, $repositoryMethod), $arguments))
        {
            throw new NotFoundHttpException();
        }
        return $resource;
    }

    protected function accessGrantedOr403(Request $request, $action)
    {
        $permission = $this->getFromConfig($request, 'permission');
        if ($permission = $permission ? $permission : true) {
            // if ($action.$this->name) {
            //     TODO: check acl with http://symfony.com/doc/current/security/acl.html
            // }
        }
    }

    public function getFromConfig(Request $request, $attribute, $required = false)
    {
        if (!isset($request->attributes->get('_elephantly')[$attribute]))
        {
            if ($required) {
                throw new InvalidConfigurationException(sprintf('The "%s" parameter has not been found in your configuration', $attribute));
            }
            return null;
        }
        return $request->attributes->get('_elephantly')[$attribute];
    }

    public function getFromQuery(Request $request, $attribute)
    {
        if (!$value = $request->query->get($attribute))
        {
            $value = $this->getFromConfig($request, $attribute);
        }
        return $value;
    }

    public function dispatch($action, $resource)
    {
        $event     = new ResourceEvent($resource);
        $eventName = $action.'.'.$this->name;
        $this->eventDispatcher->dispatch($eventName, $event);
    }

}
