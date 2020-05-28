<?php

namespace App\Controller\V1;

use App\Entity\City;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use Symfony\Component\HttpFoundation\Response;

class CityController extends AbstractFOSRestController
{
    /**
     * @Get("/cities/", name="city.list")
     */
    public function index()
    {
        $em = $this->getDoctrine()->getManager();

        $cityRepository = $em->getRepository(City::class);

        $cities = $cityRepository->findAll();

        $result = array_map(function (City $city) {
            return [
                'id' => $city->getId(),
                'name' => $city->getName(),
            ];
        }, $cities);

        return $this->view($result, Response::HTTP_OK);
    }
}