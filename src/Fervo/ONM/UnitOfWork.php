<?php

namespace Fervo\ONM;

/**
* 
*/
class UnitOfWork
{
    /**
     * A document is in MANAGED state when its persistence is managed by a DocumentManager.
     */
    const STATE_MANAGED = 1;

    /**
     * A document is new if it has just been instantiated (i.e. using the "new" operator)
     * and is not (yet) managed by a DocumentManager.
     */
    const STATE_NEW = 2;

    /**
     * A detached document is an instance with a persistent identity that is not
     * (or no longer) associated with a DocumentManager (and a UnitOfWork).
     */
    const STATE_DETACHED = 3;

    /**
     * A removed document instance is an instance with a persistent identity,
     * associated with a DocumentManager, whose persistent state has been
     * deleted (or is scheduled for deletion).
     */
    const STATE_REMOVED = 4;

    private $om;
    private $hydratorFactory;
    private $identityMap = array();
    private $documentIdentifiers = array();
    private $documentStates = array();
    private $originalDocumentData = array();

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function setHydratorFactory(HydratorFactory $hydratorFactory)
    {
        $this->hydratorFactory = $hydratorFactory;
    }

    /**
     * INTERNAL:
     * Creates a document. Used for reconstitution of documents during hydration.
     *
     * @ignore
     * @param string $className The name of the document class.
     * @param array $data The data for the document.
     * @param array $hints Any hints to account for during reconstitution/lookup of the document.
     * @return object The document instance.
     * @internal Highly performance-sensitive method.
     */
    public function getOrCreateNode($className, $data, &$hints = array())
    {
        $class = $this->om->getClassMetadata($className);

        $id = $data['_id'];
        $serializedId = serialize($id);

/*        if (isset($this->identityMap[$class->name][$serializedId])) {
            $document = $this->identityMap[$class->name][$serializedId];
            $oid = spl_object_hash($document);
            if ($document instanceof Proxy && ! $document->__isInitialized__) {
                $document->__isInitialized__ = true;
                $overrideLocalValues = true;
                if ($document instanceof NotifyPropertyChanged) {
                    $document->addPropertyChangedListener($this);
                }
            } else {
                $overrideLocalValues = ! empty($hints[Query::HINT_REFRESH]);
            }
            if ($overrideLocalValues) {
                $data = $this->hydratorFactory->hydrate($document, $data, $hints);
                $this->originalDocumentData[$oid] = $data;
            }
        } else {*/
            $document = $class->newInstance();
            $this->registerManaged($document, $id, $data);
            $oid = spl_object_hash($document);
            $this->documentStates[$oid] = self::STATE_MANAGED;
            $this->identityMap[$class->name][$serializedId] = $document;
            $this->originalDocumentData[$oid] = $data;

            $hydrator = $this->hydratorFactory->getHydratorFor($className);
            $currentData = $hydrator->extract($document);
            $mergedData = $this->mergeData($class, $currentData, $data);

            $hydrator->hydrate($mergedData, $document);
//        }
        return $document;
    }

    public function mergeData($class, $currentData, $data)
    {
        $idField = $class->getIdentifier()[0];

        foreach ($class->fieldMappings as $fm) {
            if ($fm['fieldName'] !== $idField) {
                $currentData[$fm['fieldName']] = $data[$fm['propertyName']];
            }
        }

        foreach ($class->associationMappings as $am) {
            $currentData[$am['fieldName']] = null;
        }

        $currentData[$idField] = $data['_id'];

        return $currentData;
    }

    /**
     * INTERNAL:
     * Registers a document as managed.
     *
     * TODO: This method assumes that $id is a valid PHP identifier for the
     * document class. If the class expects its database identifier to be a
     * MongoId, and an incompatible $id is registered (e.g. an integer), the
     * document identifiers map will become inconsistent with the identity map.
     * In the future, we may want to round-trip $id through a PHP and database
     * conversion and throw an exception if it's inconsistent.
     *
     * @param object $document The document.
     * @param array $id The identifier values.
     * @param array $data The original document data.
     */
    public function registerManaged($document, $id, array $data)
    {
        $oid = spl_object_hash($document);
        $class = $this->om->getClassMetadata(get_class($document));

        $this->documentIdentifiers[$oid] = $id;

        $this->documentStates[$oid] = self::STATE_MANAGED;
        $this->originalDocumentData[$oid] = $data;
        $this->addToIdentityMap($document);
    }

    /**
     * INTERNAL:
     * Registers a document in the identity map.
     *
     * Note that documents in a hierarchy are registered with the class name of
     * the root document. Identifiers are serialized before being used as array
     * keys to allow differentiation of equal, but not identical, values.
     *
     * @ignore
     * @param object $document  The document to register.
     * @return boolean  TRUE if the registration was successful, FALSE if the identity of
     *                  the document in question is already managed.
     */
    public function addToIdentityMap($document)
    {
        $class = $this->om->getClassMetadata(get_class($document));

        $id = $this->documentIdentifiers[spl_object_hash($document)];
        $id = serialize($id);

        if (isset($this->identityMap[$class->name][$id])) {
            return false;
        }

        $this->identityMap[$class->name][$id] = $document;

        return true;
    }

}
