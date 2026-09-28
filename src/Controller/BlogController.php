<?php

namespace App\Controller;

use App\Entity\Realisation;
use App\Repository\RealisationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BlogController extends AbstractController
{
    private const UPLOAD_DIR = '/public/uploads/realisations';
    private const MAX_BYTES = 12 * 1024 * 1024;
    private const IMAGE_TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];

    #[Route('/blog', name: 'app_blog')]
    public function index(RealisationRepository $realisations): Response
    {
        // Same keys as before, so blog/index.html.twig is unchanged.
        // With no project in the database, the template shows its examples.
        $projects = array_map(static fn (Realisation $r) => [
            'id' => $r->getId(),
            'title' => $r->getTitle(),
            'description' => $r->getDescription(),
            'image_before' => $r->getImageBefore(),
            'image_after' => $r->getImageAfter(),
            'location' => $r->getLocation(),
            'date' => $r->getDate()->format('d/m/Y'),
        ], $realisations->findLatest());

        return $this->render('blog/index.html.twig', [
            'projects' => $projects,
        ]);
    }

    #[Route('/admin/blog', name: 'app_admin_blog')]
    public function admin(RealisationRepository $realisations): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/blog/index.html.twig', [
            'realisations' => $realisations->findLatest(),
        ]);
    }

    #[Route('/admin/blog/create', name: 'app_admin_blog_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $errors = [];
        $values = ['title' => '', 'description' => '', 'location' => '', 'date' => (new \DateTimeImmutable())->format('Y-m-d')];

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('realisation', (string) $request->request->get('_token'))) {
                $errors[] = 'La session a expiré, merci de renvoyer le formulaire.';
            }
            foreach (array_keys($values) as $field) {
                $values[$field] = trim((string) $request->request->get($field, ''));
                if ($values[$field] === '') {
                    $errors[] = 'Le champ « '.$this->label($field).' » est obligatoire.';
                }
            }
            $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $values['date']) ?: null;
            if ($values['date'] !== '' && !$date) {
                $errors[] = 'La date du projet n\'est pas valide.';
            }
            $before = $request->files->get('image_before');
            $after = $request->files->get('image_after');
            foreach (['avant' => $before, 'après' => $after] as $name => $file) {
                if ($error = $this->checkImage($file, $name)) {
                    $errors[] = $error;
                }
            }

            if (!$errors) {
                $realisation = (new Realisation())
                    ->setTitle(mb_substr($values['title'], 0, 255))
                    ->setDescription($values['description'])
                    ->setLocation(mb_substr($values['location'], 0, 255))
                    ->setDate($date)
                    ->setImageBefore($this->store($before))
                    ->setImageAfter($this->store($after));
                $em->persist($realisation);
                $em->flush();

                $this->addFlash('success', 'La réalisation « '.$realisation->getTitle().' » est publiée sur le blog.');

                return $this->redirectToRoute('app_admin_blog');
            }
        }

        return $this->render('admin/blog/form.html.twig', [
            'errors' => $errors,
            'values' => $values,
        ], new Response(status: $errors ? 422 : 200));
    }

    #[Route('/admin/blog/{id}/delete', name: 'app_admin_blog_delete', methods: ['POST'])]
    public function delete(Realisation $realisation, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete-realisation-'.$realisation->getId(), (string) $request->request->get('_token'))) {
            foreach ([$realisation->getImageBefore(), $realisation->getImageAfter()] as $path) {
                $file = $this->getParameter('kernel.project_dir').'/public'.$path;
                if (str_starts_with($path, '/uploads/realisations/') && is_file($file)) {
                    @unlink($file);
                }
            }
            $em->remove($realisation);
            $em->flush();
            $this->addFlash('success', 'Réalisation supprimée.');
        }

        return $this->redirectToRoute('app_admin_blog');
    }

    private function checkImage(mixed $file, string $name): ?string
    {
        if (!$file instanceof UploadedFile) {
            return 'La photo '.$name.' est obligatoire.';
        }
        if (!$file->isValid()) {
            return 'La photo '.$name.' n\'a pas pu être envoyée (fichier trop lourd ?).';
        }
        if ($file->getSize() > self::MAX_BYTES) {
            return 'La photo '.$name.' dépasse 12 Mo.';
        }
        if (!isset(self::IMAGE_TYPES[$file->getMimeType()])) {
            return 'La photo '.$name.' doit être en JPG, PNG, WebP ou GIF.';
        }

        return null;
    }

    /** Moves the upload to public/uploads/realisations and returns its public path. */
    private function store(UploadedFile $file): string
    {
        $dir = $this->getParameter('kernel.project_dir').self::UPLOAD_DIR;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $name = bin2hex(random_bytes(12)).'.'.self::IMAGE_TYPES[$file->getMimeType()];
        $file->move($dir, $name);

        return '/uploads/realisations/'.$name;
    }

    private function label(string $field): string
    {
        return ['title' => 'Titre du projet', 'description' => 'Description', 'location' => 'Localisation', 'date' => 'Date du projet'][$field];
    }
}
