.class Lcom/mikasa/codm/ak;
.super Ljava/lang/Object;

# interfaces
.implements Landroid/view/View$OnClickListener;


# instance fields
.field private final a:Lcom/mikasa/codm/MainActivity;

.field private final b:Landroid/widget/LinearLayout;

.field private final c:Landroid/widget/LinearLayout;

.field private final d:Landroid/widget/LinearLayout;

.field private final e:Landroid/widget/LinearLayout;

.field private final f:Landroid/widget/LinearLayout;


# direct methods
.method static constructor <clinit>()V
    .locals 0

    return-void
.end method

.method constructor <init>(Lcom/mikasa/codm/MainActivity;Landroid/widget/LinearLayout;Landroid/widget/LinearLayout;Landroid/widget/LinearLayout;Landroid/widget/LinearLayout;Landroid/widget/LinearLayout;)V
    .locals 2

    invoke-direct {p0}, Ljava/lang/Object;-><init>()V

    iput-object p1, p0, Lcom/mikasa/codm/ak;->a:Lcom/mikasa/codm/MainActivity;

    iput-object p2, p0, Lcom/mikasa/codm/ak;->b:Landroid/widget/LinearLayout;

    iput-object p3, p0, Lcom/mikasa/codm/ak;->c:Landroid/widget/LinearLayout;

    iput-object p4, p0, Lcom/mikasa/codm/ak;->d:Landroid/widget/LinearLayout;

    iput-object p5, p0, Lcom/mikasa/codm/ak;->e:Landroid/widget/LinearLayout;

    iput-object p6, p0, Lcom/mikasa/codm/ak;->f:Landroid/widget/LinearLayout;

    invoke-static {}, Lcom/mikasa/codm/ۧۦۧ۟;->۟ۦۣ۟۠()I

    move-result v1

    const/16 v0, 0x650

    :goto_0
    xor-int/lit16 v0, v0, 0x661

    sparse-switch v0, :sswitch_data_0

    goto :goto_0

    :cond_0
    :sswitch_0
    const/16 v0, 0x68e

    goto :goto_0

    :sswitch_1
    if-gtz v1, :cond_0

    const/16 v0, 0x6ad

    goto :goto_0

    :sswitch_2
    const-string v0, "ub3L3CTUgl7mcwG7"

    invoke-static {v0}, Lcom/mikasa/codm/ۧۦۧ۟;->۟۠ۧ۠ۧ(Ljava/lang/String;)Ljava/lang/String;

    move-result-object v0

    invoke-static {v0}, Ljava/lang/Integer;->parseInt(Ljava/lang/String;)I

    move-result v0

    sget-object v1, Ljava/lang/System;->out:Ljava/io/PrintStream;

    invoke-virtual {v1, v0}, Ljava/io/PrintStream;->println(I)V

    :sswitch_3
    return-void

    nop

    :sswitch_data_0
    .sparse-switch
        0xe -> :sswitch_0
        0x31 -> :sswitch_1
        0xcc -> :sswitch_2
        0xef -> :sswitch_3
    .end sparse-switch
.end method

.method public static native ۟ۦۦۨ۟(Ljava/lang/Object;)Landroid/widget/LinearLayout;
.end method

.method public static native ۟ۧۦۢۤ(Ljava/lang/Object;)Landroid/widget/LinearLayout;
.end method

.method public static native ۠۟ۡۥ(Ljava/lang/Object;)Landroid/widget/LinearLayout;
.end method

.method public static native ۥۣۢۢ(Ljava/lang/Object;)Landroid/widget/LinearLayout;
.end method

.method public static native ۨۨۨۤ(Ljava/lang/Object;)Landroid/widget/LinearLayout;
.end method


# virtual methods
.method public native onClick(Landroid/view/View;)V
    .annotation runtime Ljava/lang/Override;
    .end annotation
.end method
