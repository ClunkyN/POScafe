function generateReceipt(orderData) {
  try {
    // Create receipt with custom size (width: 80mm, height: auto)
    const doc = new jsPDF({
      unit: "mm",
      format: [80, 297], // Standard A4 height, reduced width
    });

    const pageWidth = doc.internal.pageSize.getWidth();
    const margin = 5; // 5mm margins

    // Header - Centered with smaller font
    doc.setFontSize(12);
    doc.text("Cafe POS Receipt", pageWidth / 2, margin + 5, {
      align: "center",
    });

    // Order Details - Left aligned
    doc.setFontSize(8);
    doc.text(`Order #: ${orderData.orderNumber}`, margin, margin + 12);
    doc.text(`Date: ${new Date().toLocaleString()}`, margin, margin + 16);
    doc.text(`Customer: ${orderData.customerName}`, margin, margin + 20);

    // Table Headers
    let yPos = margin + 28;
    doc.setFontSize(8);
    doc.text("Item", margin, yPos);
    doc.text("Qty", 35, yPos, { align: "center" });
    doc.text("Price", 50, yPos, { align: "right" });
    doc.text("Total", 75, yPos, { align: "right" });

    // Separator Line
    yPos += 2;
    doc.line(margin, yPos, pageWidth - margin, yPos);
    yPos += 4;

    // Items List
    doc.setFontSize(8);
    orderData.items.forEach((item) => {
      // Truncate long item names
      const itemName =
        item.name.length > 15 ? item.name.substring(0, 12) + "..." : item.name;

      doc.text(itemName, margin, yPos);
      doc.text(item.quantity.toString(), 35, yPos, { align: "center" });
      doc.text(`PHP ${parseFloat(item.price).toFixed(2)}`, 50, yPos, {
        align: "right",
      });
      doc.text(`PHP ${(item.quantity * item.price).toFixed(2)}`, 75, yPos, {
        align: "right",
      });
      yPos += 4;
    });

    // Totals Section
    yPos += 2;
    doc.line(margin, yPos, pageWidth - margin, yPos);
    yPos += 4;

    // Right align all totals with spacing
    const labelX = 35; // Position for labels
    const valueX = 75; // Position for values

    doc.text("Total Amount:", labelX, yPos);
    doc.text(
      `PHP ${parseFloat(orderData.totalAmount).toFixed(2)}`,
      valueX,
      yPos,
      { align: "right" }
    );
    yPos += 4;

    doc.text("Amount Tendered:", labelX, yPos);
    doc.text(
      `PHP ${parseFloat(orderData.amountTendered).toFixed(2)}`,
      valueX,
      yPos,
      { align: "right" }
    );
    yPos += 4;

    doc.text("Change:", labelX, yPos);
    doc.text(
      `PHP ${(orderData.amountTendered - orderData.totalAmount).toFixed(2)}`,
      valueX,
      yPos,
      { align: "right" }
    );

    // Save PDF
    doc.save(`receipt-${orderData.orderNumber}.pdf`);
  } catch (error) {
    console.error("Error generating PDF:", error);
  }
}
