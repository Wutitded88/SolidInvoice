<?php

declare(strict_types=1);

/*
 * This file is part of SolidInvoice project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace SolidInvoice\InvoiceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Money\Money;
use SolidInvoice\CoreBundle\Entity\Discount;
use SolidInvoice\MoneyBundle\Entity\Money as MoneyEntity;
use Symfony\Component\Serializer\Annotation as Serialize;

/**
 * @ORM\MappedSuperclass()
 */
abstract class BaseInvoice
{
    /**
     * @var string|null
     *
     * @ORM\Column(name="status", type="string", length=25)
     * @Serialize\Groups({"invoice_api", "recurring_invoice_api", "client_api"})
     */
    protected $status;

    /**
     * @var MoneyEntity
     *
     * @ORM\Embedded(class="SolidInvoice\MoneyBundle\Entity\Money")
     * @Serialize\Groups({"invoice_api", "recurring_invoice_api", "client_api"})
     */
    protected $total;

    /**
     * @var MoneyEntity
     *
     * @ORM\Embedded(class="SolidInvoice\MoneyBundle\Entity\Money")
     * @Serialize\Groups({"invoice_api", "recurring_invoice_api", "client_api"})
     */
    protected $baseTotal;

    /**
     * @var MoneyEntity
     *
     * @ORM\Embedded(class="SolidInvoice\MoneyBundle\Entity\Money")
     * @Serialize\Groups({"invoice_api", "recurring_invoice_api", "client_api"})
     */
    protected $tax;

    /**
     * @var Discount
     *
     * @ORM\Embedded(class="SolidInvoice\CoreBundle\Entity\Discount")
     * @Serialize\Groups({"invoice_api", "recurring_invoice_api", "client_api", "create_invoice_api", "create_recurring_invoice_api"})
     */
    protected $discount;

    /**
     * @var string|null
     *
     * @ORM\Column(name="terms", type="text", nullable=true)
     * @Serialize\Groups({"invoice_api", "recurring_invoice_api", "client_api", "create_invoice_api", "create_recurring_invoice_api"})
     */
    protected $terms;

    /**
     * @var string|null
     *
     * @ORM\Column(name="notes", type="text", nullable=true)
     * @Serialize\Groups({"invoice_api", "recurring_invoice_api", "client_api", "create_invoice_api", "create_recurring_invoice_api"})
     */
    protected $notes;

    public function __construct()
    {
        $this->discount = new Discount();
        $this->baseTotal = new MoneyEntity();
        $this->tax = new MoneyEntity();
        $this->total = new MoneyEntity();
    }

    /**
     * Get status.
     *
     * @return string
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * Set status.
     *
     * @return Invoice
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getTotal(): Money
    {
        return $this->total->getMoney();
    }

    /**
     * Set total.
     *
     * @return Invoice
     */
    public function setTotal(Money $total): self
    {
        $this->total = new MoneyEntity($total);

        return $this;
    }

    /**
     * Get base total.
     */
    public function getBaseTotal(): Money
    {
        return $this->baseTotal->getMoney();
    }

    /**
     * Set base total.
     *
     * @return Invoice
     */
    public function setBaseTotal(Money $baseTotal): self
    {
        $this->baseTotal = new MoneyEntity($baseTotal);

        return $this;
    }

    public function getDiscount(): Discount
    {
        return $this->discount;
    }

    /**
     * Set discount.
     *
     * @return Invoice
     */
    public function setDiscount(Discount $discount): self
    {
        $this->discount = $discount;

        return $this;
    }

    /**
     * @return string
     */
    public function getTerms(): ?string
    {
        return $this->terms;
    }

    /**
     * @param string $terms
     *
     * @return Invoice
     */
    public function setTerms(?string $terms): self
    {
        $this->terms = $terms;

        return $this;
    }

    /**
     * @return string
     */
    public function getNotes(): ?string
    {
        return $this->notes;
    }

    /**
     * @param string $notes
     *
     * @return Invoice
     */
    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;

        return $this;
    }

    public function getTax(): Money
    {
        return $this->tax->getMoney();
    }

    /**
     * @return Invoice
     */
    public function setTax(Money $tax): self
    {
        $this->tax = new MoneyEntity($tax);

        return $this;
    }
}
