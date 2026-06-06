<?php
// ============================================================
// core/Paginator.php  –  Simple pagination helper
// ============================================================

class Paginator
{
    private int $total;
    private int $perPage;
    private int $currentPage;

    public function __construct(int $total, int $perPage, int $currentPage)
    {
        $this->total       = $total;
        $this->perPage     = max(1, $perPage);
        $this->currentPage = max(1, $currentPage);
    }

    /** SQL OFFSET for current page */
    public function offset(): int
    {
        return ($this->currentPage - 1) * $this->perPage;
    }

    /** Total number of pages */
    public function totalPages(): int
    {
        return (int) ceil($this->total / $this->perPage);
    }

    /** True if there is a previous page */
    public function hasPrev(): bool
    {
        return $this->currentPage > 1;
    }

    /** True if there is a next page */
    public function hasNext(): bool
    {
        return $this->currentPage < $this->totalPages();
    }

    public function currentPage(): int { return $this->currentPage; }
    public function perPage():     int { return $this->perPage;     }
    public function total():       int { return $this->total;       }

    /**
     * Return array of page numbers to display in the UI.
     * Shows a window of $range pages around the current page.
     */
    public function pages(int $range = 2): array
    {
        $pages = [];
        $start = max(1, $this->currentPage - $range);
        $end   = min($this->totalPages(), $this->currentPage + $range);

        for ($i = $start; $i <= $end; $i++) {
            $pages[] = $i;
        }
        return $pages;
    }
}
