<?php

namespace Elephantly\ResourceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
* @author purplebabar lalung.alexandre@gmail.com
*/
class GenericController extends Controller
{

    /**
    *   @var GenericRestRepositoryInterface
    */
    protected $resourceRepository;

    /**
    *   @var string
    */
    protected $resourceMetadata;


    public function __construct(GenericRestRepositoryInterface $resourceRepository,
                                $resourceMetadata){
        $this->resourceRepository    = $resourceRepository;
        $this->resourceMetadata      = $resourceMetadata;
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
        $data = $this->resourceSerializer->serialize($resource, $this->getVersion($request));

        return $this->handleResponse(JsonResponse::HTTP_OK, $data);
    }

    public function indexAction(Request $request)
    {

        // Getting parameters from
        $limit = $request->query->has('limit') ? $request->query->get('limit') : 20;
        $offset = $request->query->has('page') ? $request->query->get('page')*$limit : 0;

        $data = array();

        $resources = $this->findOr404('findBy', array(array(), null, $limit, $offset));

        return $this->handleResponse(JsonResponse::HTTP_OK, $data);

    }

    public function createAction(Request $request)
    {
        $resource = new $this->resourceMetadata();

        $resource = $this->resourceDeserializer->create($this->getContent($request), $resource, $this->getVersion($request));

        $this->resourceRepository->save($resource);

        return $this->handleResponse(JsonResponse::HTTP_CREATED, $data);

    }

    public function updateAction(Request $request, $id)
    {

        $resource = $this->findOr404('find', array($id));

        $resource = $this->resourceDeserializer->update($this->getContent($request), $resource, $this->getVersion($request));

        $this->resourceRepository->save($resource);

        return $this->handleResponse($status, $data);
    }

    public function deleteAction(Request $request, $id)
    {
        $resource = $this->findOr404('find', array($id));

        $this->resourceRepository->delete($resource);

        return $this->handleResponse($status, $data);
    }

    protected function findOr404($repositoryMethod, $arguments = array())
    {
        if (null === $resource = call_user_func_array ( array( $this->resourceRepository, $repositoryMethod), $arguments))
        {
            // throw new NotFoundJsonException();
        }
        return $resource;
    }

}
